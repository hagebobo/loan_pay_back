from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import joblib
import numpy as np
import pandas as pd
import os
import sys

app = Flask(__name__, template_folder='../Frontend')
CORS(app)

# Global variables for model components
model = None
scaler = None
label_encoders = None
feature_names = None
actual_feature_names = None

# ✅ CORRECT FEATURE ORDER based on your actual dataset
# These are the 11 features (id is dropped before training, loan_paid_back is target)
EXPECTED_FEATURE_ORDER = [
    'annual_income',        # Column_0 in model
    'debt_to_income_ratio', # Column_1 in model
    'credit_score',         # Column_2 in model
    'loan_amount',          # Column_3 in model
    'interest_rate',        # Column_4 in model
    'gender',               # Column_5 in model (encoded)
    'marital_status',       # Column_6 in model (encoded)
    'education_level',      # Column_7 in model (encoded)
    'employment_status',    # Column_8 in model (encoded)
    'loan_purpose',         # Column_9 in model (encoded)
    'grade_subgrade'        # Column_10 in model (encoded)
]

def load_joblib_file(filepath, description):
    """Safely load a joblib file with error handling"""
    try:
        if not os.path.exists(filepath):
            print(f"❌ ERROR: {filepath} not found")
            return None
        
        file_size = os.path.getsize(filepath)
        if file_size == 0:
            print(f"❌ ERROR: {filepath} is empty (0 bytes)")
            return None
        
        print(f"📂 Loading {description} from {filepath} ({file_size} bytes)...")
        
        obj = joblib.load(filepath)
        print(f"✅ {description} loaded successfully")
        return obj
            
    except Exception as e:
        print(f"❌ ERROR loading {description}: {str(e)}")
        print(f"   Error type: {type(e).__name__}")
        return None

def load_model_components():
    """Load all model components with proper error handling"""
    global feature_names, actual_feature_names
    
    print("\n" + "="*60)
    print("🔄 INITIALIZING MODEL COMPONENTS")
    print("="*60)
    
    # Load model
    model = load_joblib_file('model.pkl', 'ML Model')
    if model is not None:
        print(f"   Model type: {type(model).__name__}")
    
    # Load scaler
    scaler = load_joblib_file('scaler.pkl', 'Feature Scaler')
    if scaler is not None:
        print(f"   Scaler type: {type(scaler).__name__}")
    
    # Load label encoders
    encoders = load_joblib_file('label_encoders.pkl', 'Label Encoders')
    if encoders is not None:
        print(f"   Encoders type: {type(encoders).__name__}")
        if isinstance(encoders, dict):
            print(f"   Available encoders: {list(encoders.keys())}")
    
    # Try to get feature names from model
    feature_names = None
    if model is not None:
        try:
            if hasattr(model, 'feature_name_'):
                feature_names = list(model.feature_name_)
                print(f"✅ Extracted feature names from model: {len(feature_names)} features")
            elif hasattr(model, 'feature_names_in_'):
                feature_names = list(model.feature_names_in_)
                print(f"✅ Extracted feature names from model: {len(feature_names)} features")
        except:
            pass
    
    # Check if we have generic column names (Column_0, Column_1, etc.)
    if feature_names and any('Column_' in str(name) for name in feature_names):
        print(f"⚠️  Model has generic column names (Column_0, Column_1, ...)")
        print(f"   Using dataset-based feature mapping")
        
        # Use the actual feature order from your dataset
        actual_feature_names = EXPECTED_FEATURE_ORDER.copy()
        
        # Verify lengths match
        if len(feature_names) != len(actual_feature_names):
            print(f"❌ ERROR: Feature count mismatch!")
            print(f"   Model expects: {len(feature_names)} features")
            print(f"   Dataset has: {len(actual_feature_names)} features")
        else:
            print(f"✅ Feature mapping verified: {len(feature_names)} features")
    else:
        # Model already has proper feature names
        actual_feature_names = feature_names if feature_names else EXPECTED_FEATURE_ORDER.copy()
    
    print("\n" + "="*60)
    if model is not None and scaler is not None and encoders is not None:
        print("✅ ALL COMPONENTS LOADED SUCCESSFULLY!")
        print(f"📊 Feature Mapping ({len(actual_feature_names)} features):")
        if feature_names:
            for i, (model_name, actual_name) in enumerate(zip(feature_names, actual_feature_names)):
                print(f"   {i+1}. {model_name:15} → {actual_name}")
    else:
        print("❌ SOME COMPONENTS FAILED TO LOAD!")
    print("="*60 + "\n")
    
    return model, scaler, encoders, feature_names, actual_feature_names

# Initialize model components
model, scaler, label_encoders, feature_names, actual_feature_names = load_model_components()

@app.route('/')
def home():
    """Serve the main dashboard page"""
    return render_template('dashboard.php')

@app.route('/predict', methods=['POST'])
def predict():
    """Single loan prediction endpoint"""
    if model is None:
        return jsonify({
            'error': 'Model not loaded. Server initialization failed.',
            'details': 'Please check server logs and ensure model files are valid.'
        }), 500
    
    try:
        data = request.json
        print(f"\n📥 Received prediction request: {data.get('name', 'Unknown')}")
        
        # Create feature dictionary with values from request
        # Map frontend field names to backend feature names
        field_mapping = {
            'annual_income': 'annual_income',
            'debt_to_income_ratio': 'debt_to_income_ratio',
            'credit_score': 'credit_score',
            'loan_amount': 'loan_amount',
            'interest_rate': 'interest_rate',
            'gender': 'gender',
            'marital_status': 'marital_status',
            'education_level': 'education_level',
            'employment_status': 'employment_status',
            'loan_purpose': 'loan_purpose',
            'grade_subgrade': 'grade_subgrade'
        }
        
        # Extract features in the correct order
        feature_values = []
        for expected_feature in actual_feature_names:
            # Try to find the value from request data
            value = None
            
            # Direct match
            if expected_feature in data:
                value = data[expected_feature]
            # Check field_mapping for alternatives
            else:
                for req_field, feature_name in field_mapping.items():
                    if feature_name == expected_feature and req_field in data:
                        value = data[req_field]
                        break
            
            # Use defaults if not found
            if value is None:
                if expected_feature in ['gender', 'marital_status', 'education_level', 
                                       'employment_status', 'loan_purpose', 'grade_subgrade']:
                    value = 'Unknown'
                    print(f"⚠️  Missing {expected_feature}, using 'Unknown'")
                else:
                    value = 0
                    print(f"⚠️  Missing {expected_feature}, using 0")
            
            feature_values.append(value)
        
        # Create DataFrame with actual feature names
        df = pd.DataFrame([feature_values], columns=actual_feature_names)
        
        print(f"📊 Input features (before encoding):")
        for col in df.columns:
            print(f"   {col}: {df[col].values[0]}")
        
        # Encode categorical variables FIRST (before scaling)
        categorical_cols = ['gender', 'marital_status', 'education_level', 
                           'employment_status', 'loan_purpose', 'grade_subgrade']
        
        for col in categorical_cols:
            if col in df.columns:
                try:
                    if isinstance(label_encoders, dict) and col in label_encoders:
                        original_value = df[col].values[0]
                        # Transform returns array, so we need to get the value
                        encoded_value = label_encoders[col].transform(df[col].astype(str))[0]
                        df[col] = encoded_value
                        print(f"   ✅ Encoded {col}: '{original_value}' → {encoded_value} (numeric)")
                except Exception as e:
                    print(f"⚠️  Warning encoding {col}: {e}. Using default value 0.")
                    df[col] = 0
        
        print(f"\n📊 Features after encoding (all numeric):")
        for col in df.columns:
            print(f"   {col}: {df[col].values[0]} (type: {type(df[col].values[0]).__name__})")
        
        # Now scale the numeric features
        X_scaled = scaler.transform(df)
        print(f"✅ Features scaled successfully")
        
        # Make prediction
        prediction = model.predict(X_scaled)[0]
        
        # Get probability if available
        try:
            probability = model.predict_proba(X_scaled)[0]
            prob_not_paid = float(probability[0])
            prob_paid = float(probability[1])
            print(f"   Probability [Not Paid: {prob_not_paid:.4f}, Paid: {prob_paid:.4f}]")
        except Exception as e:
            print(f"⚠️  predict_proba not available: {e}")
            prob_paid = 1.0 if prediction == 1 else 0.0
            prob_not_paid = 1.0 - prob_paid
        
        result = {
            'prediction': 'Will Pay Back' if prediction == 1 else 'Will Not Pay Back',
            'probability_paid_back': prob_paid,
            'probability_not_paid_back': prob_not_paid,
            'confidence': max(prob_paid, prob_not_paid)
        }
        
        print(f"✅ Prediction: {result['prediction']} (confidence: {result['confidence']*100:.1f}%)")
        
        return jsonify(result)
    
    except Exception as e:
        print(f"❌ Prediction error: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'error': f'Prediction error: {str(e)}',
            'type': type(e).__name__
        }), 400

@app.route('/predict_batch', methods=['POST'])
def predict_batch():
    """Batch loan prediction endpoint"""
    if model is None:
        return jsonify({
            'error': 'Model not loaded. Server initialization failed.'
        }), 500
    
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'No file uploaded'}), 400
        
        file = request.files['file']
        print(f"\n📥 Processing batch file: {file.filename}")
        
        # Read file
        if file.filename.endswith('.csv'):
            df = pd.read_csv(file)
        elif file.filename.endswith(('.xlsx', '.xls')):
            df = pd.read_excel(file)
        else:
            return jsonify({'error': 'Unsupported file format. Use CSV or Excel.'}), 400
        
        print(f"📊 Loaded {len(df)} records with columns: {df.columns.tolist()}")
        df_original = df.copy()
        
        # Prepare features in correct order
        feature_data = []
        for idx, row in df.iterrows():
            row_features = []
            for feature in actual_feature_names:
                if feature in df.columns:
                    value = row[feature]
                else:
                    # Use default values
                    if feature in ['gender', 'marital_status', 'education_level',
                                 'employment_status', 'loan_purpose', 'grade_subgrade']:
                        value = 'Unknown'
                    else:
                        value = 0
                row_features.append(value)
            feature_data.append(row_features)
        
        df_processed = pd.DataFrame(feature_data, columns=actual_feature_names)
        print(f"📊 Processed features: {df_processed.columns.tolist()}")
        
        # Encode categorical variables (convert to numeric BEFORE scaling)
        categorical_cols = ['gender', 'marital_status', 'education_level', 
                           'employment_status', 'loan_purpose', 'grade_subgrade']
        
        for col in categorical_cols:
            if col in df_processed.columns:
                try:
                    if isinstance(label_encoders, dict) and col in label_encoders:
                        # Transform all values in the column to numeric
                        df_processed[col] = label_encoders[col].transform(df_processed[col].astype(str))
                        print(f"   ✅ Encoded {col} (converted to numeric)")
                except Exception as e:
                    print(f"   ⚠️  Error encoding {col}: {e}")
                    df_processed[col] = 0
        
        # Verify all columns are numeric before scaling
        print(f"\n📊 Data types before scaling:")
        for col in df_processed.columns:
            print(f"   {col}: {df_processed[col].dtype}")
        
        # Scale features (now all numeric)
        X_scaled = scaler.transform(df_processed)
        print(f"✅ Scaled {len(X_scaled)} samples")
        
        # Make predictions
        predictions = model.predict(X_scaled)
        
        try:
            probabilities = model.predict_proba(X_scaled)
        except:
            probabilities = np.column_stack([1 - predictions, predictions])
        
        # Add results to original dataframe
        df_original['Prediction'] = ['Will Pay Back' if p == 1 else 'Will Not Pay Back' 
                                      for p in predictions]
        df_original['Probability_Paid_Back'] = probabilities[:, 1]
        df_original['Probability_Not_Paid_Back'] = probabilities[:, 0]
        
        results = df_original.to_dict('records')
        
        summary = {
            'total': len(predictions),
            'will_pay_back': int(sum(predictions == 1)),
            'will_not_pay_back': int(sum(predictions == 0)),
            'avg_probability_paid': float(np.mean(probabilities[:, 1]))
        }
        
        print(f"✅ Batch processing complete:")
        print(f"   Total: {summary['total']}")
        print(f"   Will Pay Back: {summary['will_pay_back']} ({summary['will_pay_back']/summary['total']*100:.1f}%)")
        print(f"   Will Not Pay Back: {summary['will_not_pay_back']} ({summary['will_not_pay_back']/summary['total']*100:.1f}%)")
        
        return jsonify({
            'predictions': results,
            'summary': summary
        })
    
    except Exception as e:
        print(f"❌ Batch prediction error: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'error': f'Batch prediction error: {str(e)}',
            'type': type(e).__name__
        }), 400

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy' if model is not None else 'unhealthy',
        'model_loaded': model is not None,
        'scaler_loaded': scaler is not None,
        'encoder_loaded': label_encoders is not None,
        'features_loaded': feature_names is not None,
        'feature_count': len(feature_names) if feature_names else 0,
        'actual_features': actual_feature_names if actual_feature_names else []
    })

@app.route('/test', methods=['GET'])
def test():
    """Test endpoint to verify server is running"""
    feature_mapping = {}
    if feature_names and actual_feature_names:
        feature_mapping = dict(zip(feature_names, actual_feature_names))
    
    return jsonify({
        'message': 'Flask server is running!',
        'model_status': 'loaded' if model is not None else 'not loaded',
        'model_type': type(model).__name__ if model else None,
        'feature_mapping': feature_mapping,
        'expected_features': actual_feature_names if actual_feature_names else []
    })

@app.route('/features', methods=['GET'])
def get_features():
    """Return the expected feature names and their descriptions"""
    features_info = [
        {'name': 'annual_income', 'type': 'numeric', 'description': 'Annual income in dollars'},
        {'name': 'debt_to_income_ratio', 'type': 'numeric', 'description': 'Debt-to-income ratio (percentage)'},
        {'name': 'credit_score', 'type': 'numeric', 'description': 'Credit score (300-850)'},
        {'name': 'loan_amount', 'type': 'numeric', 'description': 'Requested loan amount in dollars'},
        {'name': 'interest_rate', 'type': 'numeric', 'description': 'Interest rate (percentage)'},
        {'name': 'gender', 'type': 'categorical', 'description': 'Gender'},
        {'name': 'marital_status', 'type': 'categorical', 'description': 'Marital status'},
        {'name': 'education_level', 'type': 'categorical', 'description': 'Education level'},
        {'name': 'employment_status', 'type': 'categorical', 'description': 'Employment status'},
        {'name': 'loan_purpose', 'type': 'categorical', 'description': 'Purpose of loan'},
        {'name': 'grade_subgrade', 'type': 'categorical', 'description': 'Loan grade/subgrade'}
    ]
    
    return jsonify({
        'features': features_info,
        'total_features': len(features_info)
    })

if __name__ == '__main__':
    print("\n" + "="*60)
    print("🚀 Starting Flask Application")
    print("="*60)
    print(f"📍 Server URL: http://127.0.0.1:5000")
    print(f"📍 Health Check: http://127.0.0.1:5000/health")
    print(f"📍 Test Endpoint: http://127.0.0.1:5000/test")
    print(f"📍 Features Info: http://127.0.0.1:5000/features")
    print("="*60 + "\n")
    
    app.run(debug=True, port=5000, host='0.0.0.0')