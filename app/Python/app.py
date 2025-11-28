from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP requests

# Load the trained models
MODEL_DIR = os.path.join(os.path.dirname(__file__), 'ML')

try:
    vectorizer = joblib.load(os.path.join(MODEL_DIR, 'lumora_autotagger_vectorizer.joblib'))
    model = joblib.load(os.path.join(MODEL_DIR, 'lumora_autotagger_model.joblib'))
    mlb = joblib.load(os.path.join(MODEL_DIR, 'lumora_autotagger_mlb.joblib'))
    print("Models loaded successfully!")
except Exception as e:
    print(f"Error loading models: {e}")
    vectorizer = None
    model = None
    mlb = None

@app.route('/health', methods=['GET'])
def health_check():
    """Check if the service is running"""
    return jsonify({
        'status': 'healthy',
        'models_loaded': all([vectorizer, model, mlb])
    })

@app.route('/predict-tags', methods=['POST'])
def predict_tags():
    """
    Predict tags for a product based on its description
    Expected JSON: {
        "product_name": "Product Name",
        "description": "Product description",
        "short_description": "Short desc" (optional)
    }
    """
    try:
        # Check if models are loaded
        if not all([vectorizer, model, mlb]):
            return jsonify({
                'success': False,
                'error': 'Models not loaded properly'
            }), 500

        # Get data from request
        data = request.get_json()
        
        if not data:
            return jsonify({
                'success': False,
                'error': 'No data provided'
            }), 400

        # Extract product information
        product_name = data.get('product_name', '')
        description = data.get('description', '')
        short_description = data.get('short_description', '')

        # Validate input
        if not product_name and not description:
            return jsonify({
                'success': False,
                'error': 'Either product_name or description is required'
            }), 400

        # Combine text for prediction (adjust based on your model training)
        combined_text = f"{product_name} {short_description} {description}".strip()

        # Transform input using vectorizer
        X = vectorizer.transform([combined_text])

        # Get predictions
        predictions = model.predict(X)

        # Convert predictions back to tag names
        predicted_tags = mlb.inverse_transform(predictions)[0]

        # Get prediction probabilities (if your model supports it)
        try:
            probabilities = model.predict_proba(X)[0]
            # Get confidence scores for predicted tags
            tag_confidences = {}
            for i, tag in enumerate(mlb.classes_):
                if tag in predicted_tags:
                    tag_confidences[tag] = float(probabilities[i])
        except:
            tag_confidences = {}

        return jsonify({
            'success': True,
            'tags': list(predicted_tags),
            'confidences': tag_confidences,
            'input_text_length': len(combined_text)
        })

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/batch-predict-tags', methods=['POST'])
def batch_predict_tags():
    """
    Predict tags for multiple products at once
    Expected JSON: {
        "products": [
            {"product_name": "...", "description": "..."},
            {"product_name": "...", "description": "..."}
        ]
    }
    """
    try:
        if not all([vectorizer, model, mlb]):
            return jsonify({
                'success': False,
                'error': 'Models not loaded properly'
            }), 500

        data = request.get_json()
        products = data.get('products', [])

        if not products:
            return jsonify({
                'success': False,
                'error': 'No products provided'
            }), 400

        results = []
        for product in products:
            product_name = product.get('product_name', '')
            description = product.get('description', '')
            short_description = product.get('short_description', '')

            combined_text = f"{product_name} {short_description} {description}".strip()
            X = vectorizer.transform([combined_text])
            predictions = model.predict(X)
            predicted_tags = mlb.inverse_transform(predictions)[0]

            results.append({
                'product_name': product_name,
                'tags': list(predicted_tags)
            })

        return jsonify({
            'success': True,
            'results': results
        })

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    # Run on localhost:5000 by default
    # For production, use a proper WSGI server like Gunicorn
    app.run(host='127.0.0.1', port=5000, debug=True)