import os
import logging
import pandas as pd
import requests
from dotenv import load_dotenv
from langdetect import detect
from transformers import pipeline

# Setup logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# Load environment variables
load_dotenv()
GROQ_API_KEY = os.getenv("GROQ_API_KEY")
GROQ_ENDPOINT = os.getenv("GROQ_ENDPOINT", "https://api.groq.com/openai/v1/chat/completions")

if not GROQ_API_KEY:
    raise ValueError("GROQ_API_KEY environment variable is not set")

# Sentiment analysis pipeline (multilingual)
sentiment_pipeline = pipeline("sentiment-analysis", model="distilbert-base-multilingual-cased")

# Load dataset
DATASET_PATH = os.path.join(os.path.dirname(__file__), "clinical_summaries.csv")
dataset_df = pd.DataFrame()
try:
    dataset_df = pd.read_csv(DATASET_PATH).dropna(subset=[
        'summary_id', 'patient_id', 'patient_age', 'patient_gender',
        'diagnosis', 'body_temp_c', 'blood_pressure_systolic',
        'heart_rate', 'summary_text', 'date_recorded'
    ])
except FileNotFoundError:
    logger.error(f"Dataset file not found at {DATASET_PATH}")
except pd.errors.EmptyDataError:
    logger.error("Dataset file is empty or malformed")
except Exception as e:
    logger.error(f"Error loading dataset: {e}")

def detect_language(text):
    """Detects the language of the input text."""
    if not text or len(text.strip()) < 3:
        return "en"
    try:
        return detect(text)
    except:
        return "en"

def detect_sentiment(text):
    """Detects the sentiment of the input text."""
    try:
        result = sentiment_pipeline(text)
        return result[0]['label']
    except:
        return "NEUTRAL"

def summarize_dataset(diagnosis=None, max_rows=3):
    """Summarizes clinical dataset records, optionally filtered by diagnosis."""
    if dataset_df.empty:
        return "No dataset available."
    
    filtered = dataset_df[dataset_df['diagnosis'].str.contains(diagnosis, case=False, na=False)] if diagnosis else dataset_df
    
    if filtered.empty:
        return f"No records found for diagnosis: {diagnosis}" if diagnosis else "No records found."
    
    summary_lines = []
    for _, row in filtered.head(max_rows).iterrows():
        line = (
            f"Age: {row.get('patient_age', 'N/A')}, "
            f"Gender: {row.get('patient_gender', 'N/A')}, "
            f"Temp: {row.get('body_temp_c', 'N/A')}°C, "
            f"BP: {row.get('blood_pressure_systolic', 'N/A')}, "
            f"HR: {row.get('heart_rate', 'N/A')}, "
            f"Note: {row.get('summary_text', '')}"
        )
        summary_lines.append(line)
    
    return "\n".join(summary_lines)

def build_prompt(user_input, cached_data, diagnosis=None):
    """Builds a dynamic prompt for the AI model based on user input and cached patient data."""
    if not user_input:
        return None, "en"
    
    # Extract patient info from cached data
    patient_info = {
        "name": cached_data.get('name', 'N/A'),
        "language": cached_data.get('language', 'en'),
        "gender": cached_data.get('gender', 'N/A'),
        "age": cached_data.get('age', 'N/A'),
        "chronic_conditions": cached_data.get('chronic_conditions', 'None'),
        "allergies": cached_data.get('allergies', 'None'),
        "region": cached_data.get('region', 'N/A'),
        "city": cached_data.get('city', 'N/A'),
        "profession": cached_data.get('profession', 'N/A'),
        "marital_status": cached_data.get('marital_status', 'N/A'),
        "lifestyle": cached_data.get('lifestyle', {})
    }
    
    detected_language = detect_language(user_input)
    language = patient_info['language'] if patient_info['language'] in ["en", "fr"] else detected_language
    sentiment = detect_sentiment(user_input)
    dataset_summary = summarize_dataset(diagnosis)

    # Dynamic tone based on sentiment
    tone = {
        "POSITIVE": "encouraging and supportive",
        "NEGATIVE": "reassuring and empathetic",
        "NEUTRAL": "calm and educational"
    }.get(sentiment.upper(), "calm and educational")

    # Adjust explanation complexity based on age and profession
    explanation_style = "simple and clear"
    if patient_info['age'] != 'N/A' and isinstance(patient_info['age'], int):
        if patient_info['age'] < 18:
            explanation_style = "very simple, using analogies suitable for a young person"
        elif patient_info['profession'].lower() in ['doctor', 'nurse', 'pharmacist', 'health']:
            explanation_style = "detailed and technical, but still clear"

    # Dynamic follow-up question based on query content
    follow_up = "Does this explanation make sense to you, or would you like more details?"
    if "pain" in user_input.lower() or "hurt" in user_input.lower():
        follow_up = "Can you tell me more about your symptoms, like how long this has been happening?"
    elif "diet" in user_input.lower() or "food" in user_input.lower():
        follow_up = "Would you like some tips on healthy eating that fit your lifestyle?"
    elif patient_info['chronic_conditions'] != 'None':
        follow_up = f"How are you managing your {patient_info['chronic_conditions']} right now?"

    # Incorporate lifestyle if available
    lifestyle_note = ""
    if patient_info['lifestyle']:
        if patient_info['lifestyle'].get('smoking', False):
            lifestyle_note = "Note that the patient smokes, so include advice on reducing health risks related to smoking."
        if patient_info['lifestyle'].get('exercise', '').lower() == 'sedentary':
            lifestyle_note = "The patient has a sedentary lifestyle, so suggest gentle physical activity if relevant."

    # Format patient info for prompt
    patient_info_str = (
        f"Name: {patient_info['name']}, "
        f"Age: {patient_info['age']}, "
        f"Gender: {patient_info['gender']}, "
        f"Chronic Conditions: {patient_info['chronic_conditions']}, "
        f"Allergies: {patient_info['allergies']}, "
        f"Profession: {patient_info['profession']}, "
        f"Marital Status: {patient_info['marital_status']}, "
        f"Location: {patient_info['region']}, {patient_info['city']}"
    )

    if language == "fr":
        intro = (
            f"Tu es un assistant médical bienveillant et éducatif pour les patients camerounais. "
            f"Réponds en français avec un ton {tone}, comme un médecin ou un enseignant attentionné. "
            f"Adapte tes explications pour être {explanation_style}, en tenant compte du profil du patient."
        )
    else:
        intro = (
            f"You are a caring and educational medical assistant for Cameroonian patients. "
            f"Respond in English with a {tone} tone, like a compassionate doctor or teacher. "
            f"Adapt your explanations to be {explanation_style}, considering the patient's profile."
        )

    prompt = f"""
    {intro}

    **Patient Profile**: {patient_info_str}
    **Context from Dataset**: {dataset_summary or 'No dataset summary found.'}
    **Patient's Current Sentiment**: {sentiment.lower()}
    **Patient's Query**: "{user_input}"
    **Lifestyle Notes**: {lifestyle_note or 'No specific lifestyle information provided.'}

    **Instructions**:
    - Respond in a warm, supportive manner, tailored to the patient’s profile, location (Cameroon), and sentiment.
    - Use {explanation_style} language to explain medical terms or concepts, avoiding jargon unless the patient is a healthcare professional.
    - Incorporate Cameroonian cultural context, such as local health practices (e.g., herbal remedies like ginger tea) or community resources in {patient_info['region']}.
    - Do not prescribe medications or treatments, but provide general health advice or explanations.
    - If relevant, reference the patient’s chronic conditions or allergies to personalize advice.
    - End with a follow-up question to ensure understanding or encourage further discussion: "{follow_up}"

    **Example Response Structure**:
    - Greet the patient by name and acknowledge their query or sentiment.
    - Provide a clear, tailored explanation or advice.
    - Include a culturally relevant suggestion if applicable (e.g., a local remedy or resource).
    - Close with the follow-up question.
    """
    return prompt.strip(), language

def generate_response(user_input, cached_data, diagnosis=None):
    """Generates a response from the AI model based on user input and cached patient data."""
    if not user_input:
        logger.error("User input is empty")
        return "Error: User input cannot be empty."
    
    if not cached_data:
        logger.error("Cached patient data is missing")
        return "Error: Patient data is required."
    
    logger.info(f"Processing user input: {user_input}")
    prompt, language = build_prompt(user_input, cached_data, diagnosis)
    if not prompt:
        logger.error("Failed to build prompt")
        return "Error: Unable to process request due to invalid input."

    headers = {
        "Authorization": f"Bearer {GROQ_API_KEY}",
        "Content-Type": "application/json"
    }
    data = {
        "model": "mixtral-8x7b-32768",
        "messages": [
            {"role": "system", "content": "You are a helpful, multilingual health assistant."},
            {"role": "user", "content": prompt}
        ],
        "temperature": 0.7
    }

    try:
        response = requests.post(GROQ_ENDPOINT, headers=headers, json=data)
        response.raise_for_status()
        result = response.json()
        if 'choices' not in result or not result['choices']:
            logger.error("Invalid response from AI service")
            return "Error: Invalid response from AI service."
        logger.info("Successfully generated response")
        return result['choices'][0]['message']['content'].strip()
    except requests.exceptions.RequestException as e:
        logger.error(f"Failed to connect to AI service: {e}")
        return f"Error: Failed to connect to AI service: {e}"
    except KeyError as e:
        logger.error(f"Unexpected response format from AI service: {e}")
        return f"Error: Unexpected response format from AI service: {e}"