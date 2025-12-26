"""
Loan Payback Prediction Tester
Run this to test predictions directly from Python
Compare results with your frontend
"""

import joblib
import numpy as np
import pandas as pd
import os

# Change to backend directory
os.chdir(r'C:\xampp\htdocs\Loan_Pay_Back\Backend')

print("="*70)
print("🔮 LOAN PAYBACK PREDICTION TESTER")
print("="*70)

# Load model components
print("\n📂 Loading model components...")
model = joblib.load('model.pkl')
scaler = joblib.load('scaler.pkl')
encoders = joblib.load('label_encoders.pkl')

print(f"✅ Model: {type(model).__name__}")
print(f"✅ Scaler: {type(scaler).__name__}")
print(f"✅ Encoders: {list(encoders.keys())}")

# Feature order (must match training)
FEATURES = [
    'annual_income',
    'debt_to_income_ratio',
    'credit_score',
    'loan_amount',
    'interest_rate',
    'gender',
    'marital_status',
    'education_level',
    'employment_status',
    'loan_purpose',
    'grade_subgrade'
]

print(f"\n📊 Feature order: {FEATURES}")

# Test cases
test_cases = [
    {
        'name': 'John Doe - Good Credit',
        'annual_income': 75000,
        'debt_to_income_ratio': 0.20,
        'credit_score': 750,
        'loan_amount': 15000,
        'interest_rate': 5.5,
        'gender': 'Male',
        'marital_status': 'Married',
        'education_level': "Bachelor's",
        'employment_status': 'Employed',
        'loan_purpose': 'Home',
        'grade_subgrade': 'A1'
    },
    {
        'name': 'Jane Smith - Medium Risk',
        'annual_income': 45000,
        'debt_to_income_ratio': 0.35,
        'credit_score': 680,
        'loan_amount': 10000,
        'interest_rate': 8.5,
        'gender': 'Female',
        'marital_status': 'Single',
        'education_level': "Bachelor's",
        'employment_status': 'Employed',
        'loan_purpose': 'Education',
        'grade_subgrade': 'B2'
    },
    {
        'name': 'Bob Johnson - High Risk',
        'annual_income': 30000,
        'debt_to_income_ratio': 0.45,
        'credit_score': 620,
        'loan_amount': 8000,
        'interest_rate': 12.0,
        'gender': 'Male',
        'marital_status': 'Divorced',
        'education_level': 'High School',
        'employment_status': 'Employed',
        'loan_purpose': 'Other',
        'grade_subgrade': 'C2'
    },
    {
        'name': 'Alice Brown - Low Risk',
        'annual_income': 95000,
        'debt_to_income_ratio': 0.15,
        'credit_score': 800,
        'loan_amount': 20000,
        'interest_rate': 4.5,
        'gender': 'Female',
        'marital_status': 'Married',
        'education_level': "Master's",
        'employment_status': 'Employed',
        'loan_purpose': 'Business',
        'grade_subgrade': 'A1'
    },
    {
        'name': 'Mike Davis - Very High Risk',
        'annual_income': 25000,
        'debt_to_income_ratio': 0.50,
        'credit_score': 580,
        'loan_amount': 5000,
        'interest_rate': 15.0,
        'gender': 'Male',
        'marital_status': 'Single',
        'education_level': 'High School',
        'employment_status': 'Unemployed',
        'loan_purpose': 'Other',
        'grade_subgrade': 'D1'
    }
]

def predict_single(data):
    """Make prediction for a single applicant"""
    
    # Extract features in correct order
    feature_values = []
    for feature in FEATURES:
        feature_values.append(data[feature])
    
    # Create DataFrame
    df = pd.DataFrame([feature_values], columns=FEATURES)
    
    print(f"\n{'='*70}")
    print(f"👤 Applicant: {data['name']}")
    print(f"{'='*70}")
    
    print("\n📋 Input Data:")
    for feature, value in zip(FEATURES, feature_values):
        print(f"   {feature:25} = {value}")
    
    # Encode categorical features
    categorical_features = ['gender', 'marital_status', 'education_level',
                           'employment_status', 'loan_purpose', 'grade_subgrade']
    
    print("\n🏷️  Encoding categorical features:")
    for feature in categorical_features:
        if feature in df.columns:
            original = df[feature].values[0]
            try:
                df[feature] = encoders[feature].transform(df[feature].astype(str))
                encoded = df[feature].values[0]
                print(f"   {feature:25} '{original}' → {encoded}")
            except Exception as e:
                print(f"   {feature:25} ERROR: {e}")
                df[feature] = 0
    
    # Scale features
    print("\n📐 Scaling features...")
    X_scaled = scaler.transform(df)
    print(f"   Shape: {X_scaled.shape}")
    print(f"   Sample values: [{X_scaled[0][0]:.3f}, {X_scaled[0][1]:.3f}, {X_scaled[0][2]:.3f}, ...]")
    
    # Make prediction
    print("\n🤖 Making prediction...")
    prediction = model.predict(X_scaled)[0]
    
    # Get probabilities
    try:
        probabilities = model.predict_proba(X_scaled)[0]
        prob_not_paid = float(probabilities[0])
        prob_paid = float(probabilities[1])
    except:
        prob_paid = 1.0 if prediction == 1 else 0.0
        prob_not_paid = 1.0 - prob_paid
    
    confidence = max(prob_paid, prob_not_paid)
    
    # Display results
    print("\n" + "="*70)
    print("🎯 PREDICTION RESULT")
    print("="*70)
    
    result_emoji = "✅" if prediction == 1 else "❌"
    result_text = "WILL PAY BACK" if prediction == 1 else "WILL NOT PAY BACK"
    
    print(f"\n{result_emoji} Prediction: {result_text}")
    print(f"📊 Probability of Paying Back:     {prob_paid*100:.2f}%")
    print(f"📊 Probability of NOT Paying Back: {prob_not_paid*100:.2f}%")
    print(f"🎲 Confidence:                     {confidence*100:.2f}%")
    
    return {
        'prediction': result_text,
        'prob_paid': prob_paid,
        'prob_not_paid': prob_not_paid,
        'confidence': confidence
    }

# Run all test cases
print("\n" + "="*70)
print("🧪 RUNNING TEST CASES")
print("="*70)

results = []
for test_case in test_cases:
    result = predict_single(test_case)
    results.append({
        'name': test_case['name'],
        **result
    })
    input("\n⏸️  Press Enter to continue to next test case...")

# Summary
print("\n" + "="*70)
print("📊 SUMMARY OF ALL PREDICTIONS")
print("="*70)

for result in results:
    emoji = "✅" if "WILL PAY" in result['prediction'] and "NOT" not in result['prediction'] else "❌"
    print(f"\n{emoji} {result['name']}")
    print(f"   Prediction: {result['prediction']}")
    print(f"   Confidence: {result['confidence']*100:.2f}%")

print("\n" + "="*70)
print("✅ TESTING COMPLETE!")
print("="*70)

print("\n💡 To test your own data:")
print("   1. Edit the test_cases list in this script")
print("   2. Run: python predictions.py")
print("   3. Compare results with frontend predictions")
print("\n🔗 Expected frontend behavior:")
print("   - Same prediction (Will/Won't Pay Back)")
print("   - Same confidence percentage")
print("   - If different → feature order might be wrong")