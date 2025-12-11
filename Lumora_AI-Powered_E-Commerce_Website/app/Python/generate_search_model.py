"""
Generate Product Metadata for Smart Search

This script creates the product_metadata.joblib file that maps product indices
to their database IDs, names, and slugs for the smart search system.

Run this script after training your TF-IDF model to ensure the metadata
matches your product vectors.
"""

import joblib
import mysql.connector
from scipy.sparse import save_npz
import os

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',  # Change to your DB user
    'password': '',  # Change to your DB password
    'database': 'lumora_db'  # Change to your database name
}

def fetch_products_from_db():
    """Fetch all published products from database"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT 
                p.product_id,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                MIN(pv.price) as price,
                GROUP_CONCAT(DISTINCT pc.name SEPARATOR ', ') as categories
            FROM products p
            LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1
            LEFT JOIN product_category_links pcl ON p.product_id = pcl.product_id
            LEFT JOIN product_categories pc ON pcl.category_id = pc.category_id
            WHERE p.status = 'PUBLISHED' 
                AND p.is_deleted = 0
            GROUP BY p.product_id
            ORDER BY p.product_id ASC
        """
        
        cursor.execute(query)
        products = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        print(f"✓ Fetched {len(products)} products from database")
        return products
        
    except mysql.connector.Error as e:
        print(f"✗ Database error: {e}")
        return []

def create_product_vectors_and_metadata(products, output_dir='ML'):
    """Create TF-IDF vectors and metadata for products"""
    from sklearn.feature_extraction.text import TfidfVectorizer
    
    if not products:
        print("✗ No products to process")
        return False
    
    # Prepare text data for each product
    product_texts = []
    product_metadata = []
    
    for product in products:
        # Combine name, description, and categories for search
        text = f"{product['name']} "
        
        if product['short_description']:
            text += f"{product['short_description']} "
        
        if product['long_description']:
            text += f"{product['long_description']} "
        
        if product['categories']:
            text += f"{product['categories']}"
        
        product_texts.append(text.strip())
        
        # Store metadata
        product_metadata.append({
            'product_id': product['product_id'],
            'name': product['name'],
            'slug': product['slug'],
            'price': float(product['price']) if product['price'] else 0.0
        })
    
    print(f"✓ Prepared {len(product_texts)} product texts")
    
    # Create TF-IDF vectorizer
    print("Creating TF-IDF vectorizer...")
    tfidf = TfidfVectorizer(
        max_features=5000,
        ngram_range=(1, 2),
        stop_words='english',
        min_df=1,
        max_df=0.95
    )
    
    # Fit and transform
    product_vectors = tfidf.fit_transform(product_texts)
    print(f"✓ Created TF-IDF vectors with shape: {product_vectors.shape}")
    
    # Create output directory if it doesn't exist
    os.makedirs(output_dir, exist_ok=True)
    
    # Save vectorizer
    vectorizer_path = os.path.join(output_dir, 'tfidf_vectorizer.joblib')
    joblib.dump(tfidf, vectorizer_path)
    print(f"✓ Saved TF-IDF vectorizer to: {vectorizer_path}")
    
    # Save product vectors
    vectors_path = os.path.join(output_dir, 'product_vectors_X.npz')
    save_npz(vectors_path, product_vectors)
    print(f"✓ Saved product vectors to: {vectors_path}")
    
    # Save metadata
    metadata_path = os.path.join(output_dir, 'product_metadata.joblib')
    joblib.dump(product_metadata, metadata_path)
    print(f"✓ Saved product metadata to: {metadata_path}")
    
    print("\n" + "="*60)
    print("SMART SEARCH MODEL GENERATION COMPLETE!")
    print("="*60)
    print(f"Total products indexed: {len(products)}")
    print(f"Vocabulary size: {len(tfidf.vocabulary_)}")
    print(f"Vector dimensions: {product_vectors.shape}")
    print("\nFiles created:")
    print(f"  1. {vectorizer_path}")
    print(f"  2. {vectors_path}")
    print(f"  3. {metadata_path}")
    print("\nYou can now use smart search by starting the Flask app:")
    print("  python app.py")
    print("="*60)
    
    return True

def main():
    print("="*60)
    print("LUMORA SMART SEARCH - MODEL GENERATION")
    print("="*60)
    print()
    
    # Fetch products from database
    print("Step 1: Fetching products from database...")
    products = fetch_products_from_db()
    
    if not products:
        print("\n✗ No products found. Please add products to your database first.")
        return
    
    print()
    print("Step 2: Creating TF-IDF vectors and metadata...")
    success = create_product_vectors_and_metadata(products)
    
    if success:
        print("\n✓ Model generation successful!")
        print("\nNext steps:")
        print("  1. Start the Flask API: python app.py")
        print("  2. Test the search endpoint: POST http://localhost:5000/search")
        print("  3. Use the smart search from your PHP application")
    else:
        print("\n✗ Model generation failed.")

if __name__ == '__main__':
    main()