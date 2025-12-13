from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import os
import traceback
import logging
import numpy as np
from scipy.sparse import load_npz
from sklearn.metrics.pairwise import cosine_similarity
import mysql.connector
from mysql.connector import pooling
from collections import defaultdict

app = Flask(__name__)

# ========== CORS CONFIGURATION ==========
# Enable CORS for your InfinityFree domain
CORS(app, resources={
    r"/*": {
        "origins": [
            "https://lumora.infinityfreeapp.com",
            "http://lumora.infinityfreeapp.com",
            "https://www.lumora.infinityfreeapp.com",
            "http://www.lumora.infinityfreeapp.com",
            # Add localhost for testing
            "http://localhost",
            "http://localhost:3000",
            "http://127.0.0.1",
            "http://127.0.0.1:3000"
        ],
        "methods": ["GET", "POST", "OPTIONS"],
        "allow_headers": ["Content-Type", "Authorization", "Accept"],
        "supports_credentials": True,
        "max_age": 3600
    }
})

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Global variables for models
MODEL_LOADED = False
SEARCH_MODEL_LOADED = False
db_pool = None

# Database configuration (from environment variables)
DB_CONFIG = {
    'host': os.environ.get('DB_HOST', 'localhost'),
    'user': os.environ.get('DB_USER', 'root'),
    'password': os.environ.get('DB_PASSWORD', ''),
    'database': os.environ.get('DB_NAME', 'lumora_db'),
    'pool_name': 'search_pool',
    'pool_size': 5
}

def init_db_pool():
    """Initialize database connection pool"""
    global db_pool
    try:
        db_pool = pooling.MySQLConnectionPool(**DB_CONFIG)
        logger.info("✓ Database pool initialized")
        return True
    except mysql.connector.Error as e:
        logger.warning(f"Database pool not available: {e}")
        logger.warning("Tag-based search features will be disabled, TF-IDF search will still work")
        return False

def get_db_connection():
    """Get database connection from pool"""
    global db_pool
    try:
        if db_pool is None:
            init_db_pool()
        if db_pool:
            return db_pool.get_connection()
        return None
    except mysql.connector.Error as e:
        logger.error(f"Database connection error: {e}")
        return None

def get_product_tags(product_ids):
    """
    Fetch tags for given product IDs from database
    Returns: dict {product_id: [tag1, tag2, ...]}
    """
    if not product_ids:
        return {}
    
    conn = get_db_connection()
    if not conn:
        return {}
    
    try:
        cursor = conn.cursor(dictionary=True)
        
        # Create placeholders for IN clause
        placeholders = ','.join(['%s'] * len(product_ids))
        
        query = f"""
            SELECT 
                ptl.product_id,
                pt.name as tag_name,
                pt.tag_id
            FROM product_tag_links ptl
            INNER JOIN product_tags pt ON ptl.tag_id = pt.tag_id
            WHERE ptl.product_id IN ({placeholders})
        """
        
        cursor.execute(query, product_ids)
        results = cursor.fetchall()
        
        # Organize tags by product_id
        product_tags = defaultdict(list)
        for row in results:
            product_tags[row['product_id']].append({
                'tag_id': row['tag_id'],
                'name': row['tag_name']
            })
        
        cursor.close()
        conn.close()
        
        return dict(product_tags)
        
    except mysql.connector.Error as e:
        logger.error(f"Error fetching tags: {e}")
        if conn:
            conn.close()
        return {}

def find_products_by_tags(tag_names, limit=20, exclude_ids=None):
    """
    Find products that have matching tags
    Returns: list of product_ids with their match scores
    """
    if not tag_names:
        return []
    
    conn = get_db_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        
        # Create LIKE conditions for each tag
        tag_conditions = ' OR '.join(['pt.name LIKE %s'] * len(tag_names))
        tag_params = [f'%{tag}%' for tag in tag_names]
        
        # Exclude certain product IDs if provided
        exclude_clause = ""
        if exclude_ids:
            exclude_placeholders = ','.join(['%s'] * len(exclude_ids))
            exclude_clause = f"AND p.product_id NOT IN ({exclude_placeholders})"
            tag_params.extend(exclude_ids)
        
        query = f"""
            SELECT 
                p.product_id,
                COUNT(DISTINCT ptl.tag_id) as tag_match_count,
                GROUP_CONCAT(DISTINCT pt.name) as matched_tags
            FROM products p
            INNER JOIN product_tag_links ptl ON p.product_id = ptl.product_id
            INNER JOIN product_tags pt ON ptl.tag_id = pt.tag_id
            WHERE ({tag_conditions})
                AND p.status = 'PUBLISHED'
                AND p.is_deleted = 0
                {exclude_clause}
            GROUP BY p.product_id
            ORDER BY tag_match_count DESC
            LIMIT %s
        """
        
        tag_params.append(limit)
        cursor.execute(query, tag_params)
        results = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return results
        
    except mysql.connector.Error as e:
        logger.error(f"Error finding products by tags: {e}")
        if conn:
            conn.close()
        return []

def hybrid_search(query, top_k=20, min_similarity=0.15, tag_boost_weight=0.3):
    """
    Hybrid search combining TF-IDF similarity and tag-based matching
    
    Args:
        query: Search query string
        top_k: Number of results to return
        min_similarity: Minimum similarity threshold for TF-IDF
        tag_boost_weight: Weight for tag-based boosting (0.0 to 1.0)
    
    Returns:
        List of products with combined scores
    """
    
    # Step 1: TF-IDF Similarity Search
    query_vector = search_tfidf.transform([query])
    similarities = cosine_similarity(query_vector, product_vectors).flatten()
    
    # Get top candidates based on similarity
    similarity_threshold = min_similarity
    top_indices = np.where(similarities >= similarity_threshold)[0]
    
    if len(top_indices) == 0:
        # If no results meet threshold, get top N anyway
        top_indices = np.argsort(similarities)[-top_k:][::-1]
    else:
        # Sort by similarity
        top_indices = top_indices[np.argsort(similarities[top_indices])[::-1]]
    
    # Limit to top_k * 2 for tag matching (we'll filter later)
    top_indices = top_indices[:top_k * 2]
    
    # Step 2: Extract product IDs and their similarity scores
    tfidf_results = []
    product_ids_for_tag_lookup = []
    
    for idx in top_indices:
        if idx < len(product_metadata):
            product_id = product_metadata[idx]['product_id']
            similarity_score = float(similarities[idx])
            
            tfidf_results.append({
                'product_id': product_id,
                'name': product_metadata[idx]['name'],
                'slug': product_metadata[idx].get('slug', ''),
                'price': product_metadata[idx].get('price', 0),
                'category': product_metadata[idx].get('category', ''),
                'similarity_score': similarity_score,
                'tag_boost_score': 0.0,
                'final_score': similarity_score,
                'matched_tags': []
            })
            
            product_ids_for_tag_lookup.append(product_id)
    
    # Step 3: Get tags for these products (only if DB is available)
    product_tags_map = get_product_tags(product_ids_for_tag_lookup)
    
    # Step 4: Extract query terms for tag matching
    query_terms = query.lower().split()
    
    # Step 5: Calculate tag boost scores
    for result in tfidf_results:
        product_id = result['product_id']
        
        if product_id in product_tags_map:
            tags = product_tags_map[product_id]
            tag_names = [tag['name'].lower() for tag in tags]
            
            # Count how many query terms match tags
            matches = 0
            matched_tags = []
            for term in query_terms:
                for tag_name in tag_names:
                    if term in tag_name or tag_name in term:
                        matches += 1
                        if tag_name not in matched_tags:
                            matched_tags.append(tag_name)
                        break
            
            # Normalize tag boost (0.0 to 1.0)
            if len(query_terms) > 0:
                tag_boost = matches / len(query_terms)
            else:
                tag_boost = 0.0
            
            result['tag_boost_score'] = tag_boost
            result['matched_tags'] = [tag['name'] for tag in tags]
    
    # Step 6: Find additional products by tags (only if DB is available)
    if db_pool:
        tag_based_products = find_products_by_tags(
            query_terms, 
            limit=10,
            exclude_ids=product_ids_for_tag_lookup
        )
        
        # Add tag-based results with lower base similarity
        for tag_product in tag_based_products:
            # Find metadata for this product
            metadata_match = None
            for meta in product_metadata:
                if meta['product_id'] == tag_product['product_id']:
                    metadata_match = meta
                    break
            
            if metadata_match:
                tag_boost = min(1.0, tag_product['tag_match_count'] / len(query_terms)) if query_terms else 0.5
                
                tfidf_results.append({
                    'product_id': tag_product['product_id'],
                    'name': metadata_match['name'],
                    'slug': metadata_match.get('slug', ''),
                    'price': metadata_match.get('price', 0),
                    'category': metadata_match.get('category', ''),
                    'similarity_score': 0.1,  # Low base similarity
                    'tag_boost_score': tag_boost,
                    'final_score': 0.0,
                    'matched_tags': tag_product['matched_tags'].split(',') if tag_product['matched_tags'] else []
                })
    
    # Step 7: Calculate final combined scores
    for result in tfidf_results:
        # Weighted combination
        result['final_score'] = (
            result['similarity_score'] * (1 - tag_boost_weight) +
            result['tag_boost_score'] * tag_boost_weight
        )
    
    # Step 8: Sort by final score and return top_k
    tfidf_results.sort(key=lambda x: x['final_score'], reverse=True)
    final_results = tfidf_results[:top_k]
    
    return final_results

# Load model components at startup
try:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    ML_DIR = os.path.join(BASE_DIR, 'ML')
    
    logger.info("=" * 60)
    logger.info("LOADING ML MODELS...")
    logger.info(f"Base Directory: {BASE_DIR}")
    logger.info(f"ML Directory: {ML_DIR}")
    logger.info("=" * 60)
    
    # ========== AUTO-TAGGING MODELS ==========
    logger.info("Loading Auto-Tagging models...")
    
    vectorizer_path = os.path.join(ML_DIR, 'lumora_autotagger_vectorizer.joblib')
    mlb_path = os.path.join(ML_DIR, 'lumora_autotagger_mlb.joblib')
    model_path = os.path.join(ML_DIR, 'lumora_autotagger_model.joblib')
    tags_path = os.path.join(ML_DIR, 'lumora_autotagger_final_tags.txt')
    
    # Check if files exist
    for path, name in [(vectorizer_path, 'vectorizer'), (mlb_path, 'mlb'), 
                       (model_path, 'model'), (tags_path, 'tags')]:
        if not os.path.exists(path):
            raise FileNotFoundError(f"{name} file not found at {path}")
    
    vectorizer = joblib.load(vectorizer_path)
    mlb = joblib.load(mlb_path)
    model = joblib.load(model_path)
    
    # Load tags list
    with open(tags_path, 'r', encoding='latin-1') as f:
        tags_list = [line.strip() for line in f]
    
    # Verify dimensions match
    num_classes_mlb = len(mlb.classes_)
    
    logger.info(f"✓ Vectorizer loaded - Vocabulary size: {len(vectorizer.vocabulary_)}")
    logger.info(f"✓ MLB loaded - Classes: {num_classes_mlb}")
    logger.info(f"✓ Model loaded - Expected output: {num_classes_mlb} classes")
    logger.info(f"✓ Tags list loaded - {len(tags_list)} tags")
    
    if len(tags_list) != num_classes_mlb:
        logger.warning(f"Tag list size ({len(tags_list)}) doesn't match MLB classes ({num_classes_mlb})")
    
    logger.info("=" * 60)
    logger.info("AUTO-TAGGING MODELS LOADED SUCCESSFULLY!")
    logger.info("=" * 60)
    MODEL_LOADED = True
    
except Exception as e:
    logger.error(f"Error loading auto-tagging models: {str(e)}")
    logger.error(traceback.format_exc())
    MODEL_LOADED = False

# ========== SMART SEARCH MODELS ==========
try:
    logger.info("Loading Smart Search models...")
    
    search_tfidf_path = os.path.join(ML_DIR, 'tfidf_vectorizer.joblib')
    product_vectors_path = os.path.join(ML_DIR, 'product_vectors_X.npz')
    product_metadata_path = os.path.join(ML_DIR, 'product_metadata.joblib')
    
    if not os.path.exists(search_tfidf_path):
        raise FileNotFoundError(f"TF-IDF vectorizer not found at {search_tfidf_path}")
    if not os.path.exists(product_vectors_path):
        raise FileNotFoundError(f"Product vectors not found at {product_vectors_path}")
    
    # Load search components
    search_tfidf = joblib.load(search_tfidf_path)
    product_vectors = load_npz(product_vectors_path)
    
    # Load product metadata if available
    if os.path.exists(product_metadata_path):
        product_metadata = joblib.load(product_metadata_path)
        logger.info(f"✓ Product metadata loaded - {len(product_metadata)} products")
    else:
        product_metadata = None
        logger.warning("Product metadata not found, will return indices only")
    
    logger.info(f"✓ Search TF-IDF loaded - Vocabulary: {len(search_tfidf.vocabulary_)}")
    logger.info(f"✓ Product vectors loaded - Shape: {product_vectors.shape}")
    logger.info("=" * 60)
    logger.info("SMART SEARCH MODELS LOADED SUCCESSFULLY!")
    logger.info("=" * 60)
    SEARCH_MODEL_LOADED = True
    
except Exception as e:
    logger.error(f"Error loading smart search models: {str(e)}")
    logger.error(traceback.format_exc())
    SEARCH_MODEL_LOADED = False

# ========== INITIALIZE DATABASE POOL ==========
logger.info("Initializing database pool for hybrid search...")
db_available = init_db_pool()
if db_available:
    logger.info("✓ Hybrid search (TF-IDF + Tags) ENABLED")
else:
    logger.info("⚠ Hybrid search DISABLED - Running in TF-IDF-only mode")

logger.info("=" * 60)
logger.info("LUMORA ML API READY")
logger.info("=" * 60)


# ========== API ENDPOINTS ==========

@app.route('/', methods=['GET'])
def home():
    """Root endpoint - API information"""
    return jsonify({
        'service': 'Lumora ML API',
        'version': '2.0.0',
        'status': 'online',
        'endpoints': {
            'health': '/health - Health check',
            'predict': '/predict (POST) - Auto-tag a product',
            'predict_tags': '/predict-tags (POST) - Legacy endpoint',
            'batch_predict': '/batch-predict-tags (POST) - Batch tagging',
            'search': '/search (POST) - Hybrid smart search (TF-IDF + Tags)',
            'similar': '/similar/<product_id> (GET) - Find similar products'
        },
        'models_loaded': {
            'auto_tagging': MODEL_LOADED,
            'smart_search': SEARCH_MODEL_LOADED,
            'hybrid_search': SEARCH_MODEL_LOADED and db_available
        }
    }), 200


@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy' if (MODEL_LOADED or SEARCH_MODEL_LOADED) else 'unhealthy',
        'auto_tagging_loaded': MODEL_LOADED,
        'smart_search_loaded': SEARCH_MODEL_LOADED,
        'database_available': db_pool is not None,
        'hybrid_search_enabled': SEARCH_MODEL_LOADED and db_pool is not None,
        'num_classes': len(mlb.classes_) if MODEL_LOADED else 0,
        'vocabulary_size': len(vectorizer.vocabulary_) if MODEL_LOADED else 0,
        'search_vocabulary_size': len(search_tfidf.vocabulary_) if SEARCH_MODEL_LOADED else 0,
        'num_products_indexed': product_vectors.shape[0] if SEARCH_MODEL_LOADED else 0,
        'search_method': 'hybrid_tfidf_tags' if (SEARCH_MODEL_LOADED and db_pool) else 'tfidf_only'
    }), 200 if (MODEL_LOADED or SEARCH_MODEL_LOADED) else 500


@app.route('/predict', methods=['POST', 'OPTIONS'])
def predict():
    """
    Main prediction endpoint - Auto-tag a product
    Alias for /predict-tags with cleaner naming
    """
    if request.method == 'OPTIONS':
        return '', 204
    
    return predict_tags()


@app.route('/predict-tags', methods=['POST', 'OPTIONS'])
def predict_tags():
    """
    Predict tags for a single product with configurable threshold
    
    Request JSON:
    {
        "product_name": "Handwoven Basket",
        "description": "Traditional Filipino basket",
        "short_description": "Beautiful basket",
        "threshold": 0.05
    }
    
    Response JSON:
    {
        "success": true,
        "tags": ["handmade", "traditional"],
        "confidences": {"handmade": 0.89, "traditional": 0.76},
        "threshold_used": 0.05
    }
    """
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        # Check if models are loaded
        if not MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Auto-tagging models not loaded. Check server logs.'
            }), 503
        
        # Get JSON data
        data = request.get_json()
        
        if not data:
            return jsonify({
                'success': False,
                'error': 'No data provided. Send JSON with product details.'
            }), 400
        
        # Extract fields
        product_name = data.get('product_name', '')
        description = data.get('description', '')
        short_description = data.get('short_description', '')
        threshold = data.get('threshold', 0.05)  # Lower threshold for better recall
        
        # Validate input
        if not product_name and not description:
            return jsonify({
                'success': False,
                'error': 'Either product_name or description must be provided'
            }), 400
        
        # Combine text for prediction
        combined_text = f"{product_name} {short_description} {description}".strip()
        
        logger.info(f"Predicting tags for: {combined_text[:100]}... (threshold: {threshold})")
        
        # Transform text
        text_vectorized = vectorizer.transform([combined_text])
        
        # Get probability predictions
        if hasattr(model, 'predict_proba'):
            probabilities = model.predict_proba(text_vectorized)[0]
            
            # Verify dimensions match
            if len(probabilities) != len(mlb.classes_):
                logger.error(f"Dimension mismatch! Probabilities: {len(probabilities)}, MLB: {len(mlb.classes_)}")
                return jsonify({
                    'success': False,
                    'error': 'Model dimension mismatch. Please retrain all models together.'
                }), 500
            
            # Apply custom threshold
            predictions = (probabilities >= threshold).astype(int)
            predictions = predictions.reshape(1, -1)
            
            # Get predicted tags
            predicted_labels = mlb.inverse_transform(predictions)[0]
            
            # Get confidence scores
            predicted_indices = np.where(predictions[0] == 1)[0]
            confidences = {
                mlb.classes_[idx]: float(probabilities[idx]) 
                for idx in predicted_indices
            }
            
            # Top probabilities for debugging
            all_probs = {
                mlb.classes_[idx]: float(probabilities[idx])
                for idx in range(len(probabilities))
            }
            top_probs = dict(sorted(all_probs.items(), key=lambda x: x[1], reverse=True)[:10])
            
        else:
            # Fallback for models without predict_proba
            predictions = model.predict(text_vectorized)
            predicted_labels = mlb.inverse_transform(predictions)[0]
            confidences = {}
            top_probs = {}
        
        logger.info(f"Predicted {len(predicted_labels)} tags: {', '.join(predicted_labels)}")
        
        return jsonify({
            'success': True,
            'tags': list(predicted_labels),
            'confidences': confidences,
            'threshold_used': threshold,
            'top_probabilities': top_probs,
            'input': {
                'product_name': product_name,
                'text_length': len(combined_text)
            }
        }), 200
        
    except Exception as e:
        logger.error(f"Error in predict_tags: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/similar/<int:product_id>', methods=['GET', 'OPTIONS'])
def find_similar(product_id):
    """
    Find similar products based on product ID
    
    URL Parameters:
        product_id: ID of the product to find similar items for
    
    Query Parameters:
        top_k: Number of similar products to return (default: 10)
        tag_boost_weight: Weight for tag matching (default: 0.4)
    """
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        if not SEARCH_MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Search models not loaded'
            }), 503
        
        # Find product in metadata
        product = None
        for meta in product_metadata:
            if meta['product_id'] == product_id:
                product = meta
                break
        
        if not product:
            return jsonify({
                'success': False,
                'error': 'Product not found'
            }), 404
        
        # Get parameters
        top_k = request.args.get('top_k', 10, type=int)
        tag_boost_weight = request.args.get('tag_boost_weight', 0.4, type=float)
        
        # Validate parameters
        top_k = max(1, min(50, top_k))
        tag_boost_weight = max(0.0, min(1.0, tag_boost_weight))
        
        # Create search query from product name and description
        query = f"{product['name']}"
        
        logger.info(f"Finding similar products for: {product['name']} (ID: {product_id})")
        
        # Perform hybrid search with higher tag weight and lower similarity threshold
        results = hybrid_search(query, top_k + 1, 0.1, tag_boost_weight)
        
        # Filter out the current product
        results = [r for r in results if r['product_id'] != product_id][:top_k]
        
        # Add rank
        for rank, result in enumerate(results, start=1):
            result['rank'] = rank
        
        return jsonify({
            'success': True,
            'product_id': product_id,
            'product_name': product['name'],
            'results': results,
            'total_results': len(results),
            'search_method': 'hybrid_tfidf_tags' if db_pool else 'tfidf_only'
        }), 200
        
    except Exception as e:
        logger.error(f"Similar products error: {str(e)}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/batch-predict-tags', methods=['POST', 'OPTIONS'])
def batch_predict_tags():
    """
    Predict tags for multiple products
    
    Request JSON:
    {
        "products": [
            {
                "product_name": "Product 1",
                "description": "Description 1"
            },
            {
                "product_name": "Product 2",
                "description": "Description 2"
            }
        ],
        "threshold": 0.3
    }
    """
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        if not MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Auto-tagging models not loaded'
            }), 503
        
        data = request.get_json()
        products = data.get('products', [])
        threshold = data.get('threshold', 0.3)
        
        if not products:
            return jsonify({
                'success': False,
                'error': 'No products provided'
            }), 400
        
        results = []
        
        for idx, product in enumerate(products):
            try:
                product_name = product.get('product_name', '')
                description = product.get('description', '')
                short_description = product.get('short_description', '')
                
                combined_text = f"{product_name} {short_description} {description}".strip()
                
                if not combined_text:
                    results.append({
                        'product_name': product_name,
                        'tags': [],
                        'error': 'No text content'
                    })
                    continue
                
                # Transform and predict
                text_vectorized = vectorizer.transform([combined_text])
                
                if hasattr(model, 'predict_proba'):
                    probabilities = model.predict_proba(text_vectorized)[0]
                    predictions = (probabilities >= threshold).astype(int)
                    predictions = predictions.reshape(1, -1)
                    predicted_labels = mlb.inverse_transform(predictions)[0]
                    
                    predicted_indices = np.where(predictions[0] == 1)[0]
                    confidences = {
                        mlb.classes_[idx]: float(probabilities[idx]) 
                        for idx in predicted_indices
                    }
                else:
                    predictions = model.predict(text_vectorized)
                    predicted_labels = mlb.inverse_transform(predictions)[0]
                    confidences = {}
                
                results.append({
                    'product_name': product_name,
                    'tags': list(predicted_labels),
                    'confidences': confidences,
                    'error': None
                })
                
            except Exception as e:
                logger.error(f"Error processing product {idx}: {str(e)}")
                results.append({
                    'product_name': product.get('product_name', f'Product {idx}'),
                    'tags': [],
                    'error': str(e)
                })
        
        return jsonify({
            'success': True,
            'results': results,
            'threshold_used': threshold,
            'total_products': len(products)
        }), 200
        
    except Exception as e:
        logger.error(f"Error in batch_predict_tags: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@app.route('/search', methods=['POST', 'OPTIONS'])
def smart_search():
    """
    Hybrid smart search endpoint using TF-IDF similarity + tag-based boosting
    
    Request JSON:
    {
        "query": "handwoven basket",
        "top_k": 20,
        "min_similarity": 0.15,
        "tag_boost_weight": 0.3
    }
    
    Response JSON:
    {
        "success": true,
        "query": "handwoven basket",
        "results": [
            {
                "product_id": 123,
                "name": "Handwoven Basket",
                "similarity_score": 0.85,
                "tag_boost_score": 1.0,
                "final_score": 0.895,
                "matched_tags": ["handwoven", "basket"],
                "rank": 1
            }
        ],
        "search_method": "hybrid_tfidf_tags"
    }
    """
    if request.method == 'OPTIONS':
        return '', 204
    
    try:
        if not SEARCH_MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Search models not loaded. Please check server logs.'
            }), 503
        
        # Get request data
        data = request.get_json()
        
        if not data or 'query' not in data:
            return jsonify({
                'success': False,
                'error': 'No query provided. Send JSON with "query" field.'
            }), 400
        
        query = data.get('query', '').strip()
        top_k = data.get('top_k', 20)
        min_similarity = data.get('min_similarity', 0.15)
        tag_boost_weight = data.get('tag_boost_weight', 0.3)
        
        if not query:
            return jsonify({
                'success': False,
                'error': 'Query cannot be empty'
            }), 400
        
        # Validate parameters
        top_k = max(1, min(100, int(top_k)))
        min_similarity = max(0.0, min(1.0, float(min_similarity)))
        tag_boost_weight = max(0.0, min(1.0, float(tag_boost_weight)))
        
        logger.info(f"Search query: '{query}' (top_k={top_k}, min_sim={min_similarity}, tag_weight={tag_boost_weight})")
        
        # Perform hybrid search
        results = hybrid_search(query, top_k, min_similarity, tag_boost_weight)
        
        # Add rank to results
        for rank, result in enumerate(results, start=1):
            result['rank'] = rank
        
        search_method = 'hybrid_tfidf_tags' if db_pool else 'tfidf_only'
        
        logger.info(f"Found {len(results)} matching products using {search_method}")
        
        return jsonify({
            'success': True,
            'query': query,
            'results': results,
            'total_results': len(results),
            'search_method': search_method,
            'parameters': {
                'top_k': top_k,
                'min_similarity': min_similarity,
                'tag_boost_weight': tag_boost_weight
            }
        }), 200
        
    except Exception as e:
        logger.error(f"Error in smart_search: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


# Error handlers
@app.errorhandler(404)
def not_found(error):
    return jsonify({
        'success': False,
        'error': 'Endpoint not found',
        'available_endpoints': ['/health', '/predict', '/search', '/similar/<id>', '/batch-predict-tags']
    }), 404


@app.errorhandler(500)
def internal_error(error):
    return jsonify({
        'success': False,
        'error': 'Internal server error'
    }), 500


# Main entry point (FIXED)
#if __name__ == '__main__':
#    port = int(os.environ.get('PORT', 5000))
#    logger.info(f"Starting Lumora ML API on port {port}...")
#    app.run(host='0.0.0.0', port=port, debug=False)