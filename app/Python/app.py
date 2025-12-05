from flask import Flask, request, jsonify
import joblib
import os
import traceback
import logging
import numpy as np
from scipy.sparse import load_npz
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

# Set up logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

# Global variables for models
MODEL_LOADED = False
SEARCH_MODEL_LOADED = False

# Load model components at startup
try:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    ML_DIR = os.path.join(BASE_DIR, 'ML')
    
    logger.info("Loading ML models...")
    logger.info(f"ML Directory: {ML_DIR}")
    
    # ========== AUTO-TAGGING MODELS ==========
    # Load all components from the SAME training session
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
    num_classes_model = model.estimators_[0].coef_.shape[1] if hasattr(model, 'estimators_') else None
    
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
    
    # Load TF-IDF vectorizer for search
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
    
    # Load product metadata if available (product IDs, names, etc.)
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

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy' if (MODEL_LOADED or SEARCH_MODEL_LOADED) else 'unhealthy',
        'auto_tagging_loaded': MODEL_LOADED,
        'smart_search_loaded': SEARCH_MODEL_LOADED,
        'num_classes': len(mlb.classes_) if MODEL_LOADED else 0,
        'vocabulary_size': len(vectorizer.vocabulary_) if MODEL_LOADED else 0,
        'search_vocabulary_size': len(search_tfidf.vocabulary_) if SEARCH_MODEL_LOADED else 0,
        'num_products_indexed': product_vectors.shape[0] if SEARCH_MODEL_LOADED else 0
    }), 200 if (MODEL_LOADED or SEARCH_MODEL_LOADED) else 500

@app.route('/search', methods=['POST'])
def smart_search():
    """
    Smart search endpoint using TF-IDF and cosine similarity
    
    Request JSON:
    {
        "query": "gold earrings",
        "top_k": 10,
        "min_similarity": 0.1
    }
    
    Response JSON:
    {
        "success": true,
        "query": "gold earrings",
        "results": [
            {
                "product_id": 123,
                "similarity_score": 0.85,
                "rank": 1
            },
            ...
        ],
        "total_results": 10
    }
    """
    try:
        if not SEARCH_MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Search models not loaded. Please train and save models first.'
            }), 500
        
        # Get request data
        data = request.get_json()
        
        if not data or 'query' not in data:
            return jsonify({
                'success': False,
                'error': 'No query provided. Send JSON with "query" field.'
            }), 400
        
        query = data.get('query', '').strip()
        top_k = data.get('top_k', 10)  # Number of results to return
        min_similarity = data.get('min_similarity', 0.1)  # Minimum similarity threshold
        
        if not query:
            return jsonify({
                'success': False,
                'error': 'Query cannot be empty'
            }), 400
        
        logger.info(f"Search query: '{query}' (top_k={top_k}, min_similarity={min_similarity})")
        
        # Transform query using TF-IDF
        query_vector = search_tfidf.transform([query])
        logger.debug(f"Query vector shape: {query_vector.shape}")
        
        # Calculate cosine similarity between query and all products
        similarities = cosine_similarity(query_vector, product_vectors).flatten()
        logger.debug(f"Similarities shape: {similarities.shape}")
        
        # Get indices of top-k most similar products
        # Filter by minimum similarity threshold
        valid_indices = np.where(similarities >= min_similarity)[0]
        
        if len(valid_indices) == 0:
            logger.info(f"No products found with similarity >= {min_similarity}")
            return jsonify({
                'success': True,
                'query': query,
                'results': [],
                'total_results': 0,
                'message': 'No products found matching your search'
            }), 200
        
        # Sort by similarity score (descending)
        sorted_indices = valid_indices[np.argsort(similarities[valid_indices])[::-1]]
        
        # Take top k results
        top_indices = sorted_indices[:top_k]
        top_similarities = similarities[top_indices]
        
        # Build results
        results = []
        for rank, (idx, score) in enumerate(zip(top_indices, top_similarities), start=1):
            result = {
                'rank': rank,
                'similarity_score': float(score),
                'product_index': int(idx)
            }
            
            # Add metadata if available
            if product_metadata is not None and idx < len(product_metadata):
                metadata = product_metadata[idx]
                result.update({
                    'product_id': metadata.get('product_id'),
                    'name': metadata.get('name'),
                    'slug': metadata.get('slug')
                })
            
            results.append(result)
        
        logger.info(f"Found {len(results)} products (similarity >= {min_similarity})")
        
        return jsonify({
            'success': True,
            'query': query,
            'results': results,
            'total_results': len(results),
            'parameters': {
                'top_k': top_k,
                'min_similarity': min_similarity
            }
        }), 200
        
    except Exception as e:
        logger.error(f"Error in smart_search: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc() if app.debug else None
        }), 500

@app.route('/predict-tags', methods=['POST'])
def predict_tags():
    """Predict tags for a single product with configurable threshold"""
    try:
        # Check if models are loaded
        if not MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Auto-tagging models not loaded. Check server logs.'
            }), 500
        
        # Get JSON data
        data = request.get_json()
        
        if not data:
            return jsonify({
                'success': False,
                'error': 'No data provided'
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
        logger.debug(f"Text vectorized shape: {text_vectorized.shape}")
        
        # Get probability predictions
        if hasattr(model, 'predict_proba'):
            probabilities = model.predict_proba(text_vectorized)[0]
            logger.debug(f"Probabilities shape: {probabilities.shape}")
            
            # Verify dimensions match
            if len(probabilities) != len(mlb.classes_):
                logger.error(f"Dimension mismatch! Probabilities: {len(probabilities)}, MLB: {len(mlb.classes_)}")
                return jsonify({
                    'success': False,
                    'error': f'Model dimension mismatch. Please retrain all models together.'
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
            # Fallback
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
            'top_probabilities': top_probs
        }), 200
        
    except Exception as e:
        logger.error(f"Error in predict_tags: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc() if app.debug else None
        }), 500

@app.route('/batch-predict-tags', methods=['POST'])
def batch_predict_tags():
    """Predict tags for multiple products with configurable threshold"""
    try:
        if not MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Auto-tagging models not loaded'
            }), 500
        
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
            'threshold_used': threshold
        }), 200
        
    except Exception as e:
        logger.error(f"Error in batch_predict_tags: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    app.run(debug=True, host='127.0.0.1', port=5000)