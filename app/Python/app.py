from flask import Flask, request, jsonify
import joblib
import os
import traceback
import logging
import numpy as np

app = Flask(__name__)

# Set up logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

# Load model components at startup
try:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    ML_DIR = os.path.join(BASE_DIR, 'ML')
    
    logger.info("Loading ML models...")
    logger.info(f"ML Directory: {ML_DIR}")
    
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
    logger.info("ALL MODELS LOADED SUCCESSFULLY!")
    logger.info("=" * 60)
    MODEL_LOADED = True
    
except Exception as e:
    logger.error(f"Error loading models: {str(e)}")
    logger.error(traceback.format_exc())
    MODEL_LOADED = False

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    if MODEL_LOADED:
        return jsonify({
            'status': 'healthy', 
            'model_loaded': True,
            'num_classes': len(mlb.classes_),
            'vocabulary_size': len(vectorizer.vocabulary_)
        }), 200
    else:
        return jsonify({
            'status': 'unhealthy', 
            'model_loaded': False
        }), 500

@app.route('/predict-tags', methods=['POST'])
def predict_tags():
    """Predict tags for a single product with configurable threshold"""
    try:
        # Check if models are loaded
        if not MODEL_LOADED:
            return jsonify({
                'success': False,
                'error': 'Models not loaded. Check server logs.'
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
        threshold = data.get('threshold', 0.05)  # Lower threshold for better recall (default 0.3)
        
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
        
        # Get probability predictions instead of binary predictions
        if hasattr(model, 'predict_proba'):
            # Get probabilities for each tag
            probabilities = model.predict_proba(text_vectorized)[0]
            logger.debug(f"Probabilities shape: {probabilities.shape}")
            
            # Verify dimensions match
            if len(probabilities) != len(mlb.classes_):
                logger.error(f"Dimension mismatch! Probabilities: {len(probabilities)}, MLB: {len(mlb.classes_)}")
                return jsonify({
                    'success': False,
                    'error': f'Model dimension mismatch. Please retrain all models together.'
                }), 500
            
            # Apply custom threshold to get predictions
            predictions = (probabilities >= threshold).astype(int)
            predictions = predictions.reshape(1, -1)  # Reshape for inverse_transform
            
            # Get predicted tags
            predicted_labels = mlb.inverse_transform(predictions)[0]
            
            # Get confidence scores for predicted tags
            predicted_indices = np.where(predictions[0] == 1)[0]
            confidences = {
                mlb.classes_[idx]: float(probabilities[idx]) 
                for idx in predicted_indices
            }
            
            # Also return top probabilities for all tags (for debugging)
            all_probs = {
                mlb.classes_[idx]: float(probabilities[idx])
                for idx in range(len(probabilities))
            }
            top_probs = dict(sorted(all_probs.items(), key=lambda x: x[1], reverse=True)[:10])
            
        else:
            # Fallback to default predict if predict_proba not available
            logger.warning("Model does not support predict_proba, using default threshold")
            predictions = model.predict(text_vectorized)
            logger.debug(f"Predictions shape: {predictions.shape}")
            
            if predictions.shape[1] != len(mlb.classes_):
                logger.error(f"Dimension mismatch! Predictions: {predictions.shape[1]}, MLB: {len(mlb.classes_)}")
                return jsonify({
                    'success': False,
                    'error': f'Model dimension mismatch. Please retrain all models together.'
                }), 500
            
            predicted_labels = mlb.inverse_transform(predictions)[0]
            confidences = {}
            top_probs = {}
        
        logger.info(f"Predicted {len(predicted_labels)} tags: {', '.join(predicted_labels)}")
        
        return jsonify({
            'success': True,
            'tags': list(predicted_labels),
            'confidences': confidences,
            'threshold_used': threshold,
            'top_probabilities': top_probs  # For debugging threshold tuning
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
                'error': 'Models not loaded'
            }), 500
        
        data = request.get_json()
        products = data.get('products', [])
        threshold = data.get('threshold', 0.3)  # Lower threshold for better recall
        
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
                    # Use probability-based prediction with threshold
                    probabilities = model.predict_proba(text_vectorized)[0]
                    predictions = (probabilities >= threshold).astype(int)
                    predictions = predictions.reshape(1, -1)
                    predicted_labels = mlb.inverse_transform(predictions)[0]
                    
                    # Get confidences for predicted tags
                    predicted_indices = np.where(predictions[0] == 1)[0]
                    confidences = {
                        mlb.classes_[idx]: float(probabilities[idx]) 
                        for idx in predicted_indices
                    }
                else:
                    # Fallback to default predict
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