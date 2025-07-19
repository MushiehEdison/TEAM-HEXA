from flask import Blueprint, request, jsonify, current_app
import jwt
import datetime
from . import db, bcrypt
from .models import User, Conversation
from .ai_engine import generate_response
import logging

logging.basicConfig(level=logging.DEBUG)

auth_bp = Blueprint('auth', __name__, url_prefix='/api/auth')

@auth_bp.route('/signup', methods=['POST'])
def signup():
    logging.debug("Received signup request")
    try:
        data = request.get_json()
    except Exception as e:
        logging.error(f"Invalid JSON input: {str(e)}")
        return jsonify({'message': 'Invalid JSON format'}), 400
    logging.debug(f"Request data: {data}")
    required_fields = ['name', 'username', 'email', 'password', 'phone', 'language', 'gender']
    
    if not all(field in data for field in required_fields):
        logging.error("Missing required fields")
        return jsonify({'message': 'Missing required fields'}), 400

    if len(data['name']) < 2 or len(data['name']) > 120:
        logging.error("Invalid name length")
        return jsonify({'message': 'Name must be 2-120 characters'}), 400
    if len(data['username']) < 3 or len(data['username']) > 20:
        logging.error("Invalid username length")
        return jsonify({'message': 'Username must be 3-20 characters'}), 400
    if not data['username'].replace('_', '').replace('-', '').isalnum():
        logging.error("Invalid username characters")
        return jsonify({'message': 'Username can only contain letters, numbers, underscores, or hyphens'}), 400
    if User.query.filter_by(username=data['username']).first():
        logging.error("Username already exists")
        return jsonify({'message': 'Username already exists'}), 400
    if User.query.filter_by(email=data['email']).first():
        logging.error("Email already exists")
        return jsonify({'message': 'Email already exists'}), 400
    if not data['email'] or not '@' in data['email'] or len(data['email']) > 120:
        logging.error("Invalid email format")
        return jsonify({'message': 'Invalid email format'}), 400
    if len(data['password']) < 8:
        logging.error("Password too short")
        return jsonify({'message': 'Password must be at least 8 characters'}), 400
    if len(data['phone']) > 15:
        logging.error("Phone number too long")
        return jsonify({'message': 'Phone number cannot exceed 15 characters'}), 400
    if User.query.filter_by(phone=data['phone']).first():
        logging.error("Phone number already exists")
        return jsonify({'message': 'Phone number already exists'}), 400
    if len(data['language']) > 32:
        logging.error("Language too long")
        return jsonify({'message': 'Language cannot exceed 32 characters'}), 400
    if data['gender'] not in ['Male', 'Female', 'Other']:
        logging.error("Invalid gender selection")
        return jsonify({'message': 'Invalid gender selection'}), 400

    try:
        hashed_password = bcrypt.generate_password_hash(data['password']).decode('utf-8')
        logging.debug("Password hashed")
        new_user = User(
            name=data['name'],
            username=data['username'],
            email=data['email'],
            password=hashed_password,
            phone=data['phone'],
            language=data['language'],
            gender=data['gender']
        )
        db.session.add(new_user)
        db.session.commit()
        logging.debug("User created successfully")
        token = jwt.encode(
            {
                'user_id': new_user.id,
                'exp': datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(hours=24)
            },
            current_app.config['SECRET_KEY'],
            algorithm='HS256'
        )
        logging.debug(f"JWT token generated: {token}")
        return jsonify({
            'token': token,
            'user': {
                'id': new_user.id,
                'username': new_user.username,
                'email': new_user.email
            }
        }), 201
    except Exception as e:
        db.session.rollback()
        logging.error(f"Error creating user: {str(e)}")
        return jsonify({'message': f'Error creating user: {str(e)}'}), 500

@auth_bp.route('/signin', methods=['POST'])
def signin():
    logging.debug("Received signin request")
    try:
        data = request.get_json()
    except Exception as e:
        logging.error(f"Invalid JSON input: {str(e)}")
        return jsonify({'message': 'Invalid JSON format'}), 400
    logging.debug(f"Request data: {data}")
    if not data or 'email' not in data or 'password' not in data:
        logging.error("Email and password are required")
        return jsonify({'message': 'Email and password are required'}), 400

    try:
        user = User.query.filter_by(email=data['email']).first()
        logging.debug(f"User query result: {user}")
        if not user or not bcrypt.check_password_hash(user.password, data['password']):
            logging.error("Invalid credentials")
            return jsonify({'message': 'Invalid credentials'}), 401

        token = jwt.encode(
            {
                'user_id': user.id,
                'exp': datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(hours=24)
            },
            current_app.config['SECRET_KEY'],
            algorithm='HS256'
        )
        logging.debug(f"JWT token generated: {token}")
        return jsonify({
            'token': token,
            'user': {
                'id': user.id,
                'username': user.username,
                'email': user.email
            }
        }), 200
    except Exception as e:
        logging.error(f"Error during signin: {str(e)}")
        return jsonify({'message': f'Error during signin: {str(e)}'}), 500

@auth_bp.route('/verify', methods=['GET'])
def verify():
    logging.debug("Received verify request")
    auth_header = request.headers.get('Authorization')
    logging.debug(f"Authorization header: {auth_header}")
    if not auth_header or not auth_header.startswith('Bearer '):
        logging.error("Missing or invalid token")
        return jsonify({'message': 'Missing or invalid token'}), 401

    token = auth_header.split(' ')[1]
    logging.debug(f"Token: {token}")
    try:
        data = jwt.decode(token, current_app.config['SECRET_KEY'], algorithms=['HS256'], options={'verify_exp': True})
        logging.debug(f"Decoded token data: {data}")
        user = User.query.get(data['user_id'])
        logging.debug(f"User query result: {user}")
        if not user:
            logging.error("User not found")
            return jsonify({'message': 'User not found'}), 404
        logging.debug("User verified successfully")
        return jsonify({
            'user': {
                'id': user.id,
                'username': user.username,
                'email': user.email
            }
        }), 200
    except jwt.ExpiredSignatureError as e:
        logging.error(f"Token expired: {str(e)}")
        return jsonify({'message': 'Token has expired'}), 401
    except jwt.InvalidTokenError as e:
        logging.error(f"Invalid token: {str(e)}")
        return jsonify({'message': 'Invalid token'}), 401
    except Exception as e:
        logging.error(f"Error during verify: {str(e)}")
        return jsonify({'message': f'Error during verify: {str(e)}'}), 500

@auth_bp.route('/conversation', methods=['GET', 'POST'])
def conversation():
    logging.debug("Received conversation request")
    auth_header = request.headers.get('Authorization')
    if not auth_header or not auth_header.startswith('Bearer '):
        logging.error("Missing or invalid token")
        return jsonify({'message': 'Missing or invalid token'}), 401

    token = auth_header.split(' ')[1]
    try:
        data = jwt.decode(token, current_app.config['SECRET_KEY'], algorithms=['HS256'], options={'verify_exp': True})
        user = User.query.get(data['user_id'])
        if not user:
            logging.error("User not found")
            return jsonify({'message': 'User not found'}), 404
    except jwt.ExpiredSignatureError as e:
        logging.error(f"Token expired: {str(e)}")
        return jsonify({'message': 'Token has expired'}), 401
    except jwt.InvalidTokenError as e:
        logging.error(f"Invalid token: {str(e)}")
        return jsonify({'message': 'Invalid token'}), 401
    except Exception as e:
        logging.error(f"Error decoding token: {str(e)}")
        return jsonify({'message': f'Error decoding token: {str(e)}'}), 500

    if request.method == 'GET':
        try:
            conversation = Conversation.query.filter_by(user_id=user.id).order_by(Conversation.created_at.desc()).first()
            messages = conversation.messages if conversation else []
            logging.debug(f"Fetched messages: {messages}")
            return jsonify({'messages': messages}), 200
        except Exception as e:
            logging.error(f"Error fetching conversation: {str(e)}")
            return jsonify({'message': f'Error fetching conversation: {str(e)}'}), 500

    elif request.method == 'POST':
        try:
            data = request.get_json()
            logging.debug(f"Conversation data: {data}")
            if not data or 'message' not in data:
                logging.error("Message is required")
                return jsonify({'message': 'Message is required'}), 400

            conversation = Conversation.query.filter_by(user_id=user.id).order_by(Conversation.created_at.desc()).first()
            if not conversation:
                conversation = Conversation(user_id=user.id, messages=[])
                db.session.add(conversation)

            # Append user message
            user_message = {
                'id': f"msg-{len(conversation.messages) + 1}",
                'text': data['message'],
                'isUser': True,
                'timestamp': datetime.datetime.now().strftime('%I:%M %p')
            }
            conversation.messages.append(user_message)

            # Prepare cached_data for AI engine
            cached_data = {
                'name': user.name,
                'language': user.language,
                'gender': user.gender,
                'age': 'N/A',  # Update if User model includes age
                'chronic_conditions': 'None',  # Update if available
                'allergies': 'None',  # Update if available
                'region': 'N/A',  # Update if available
                'city': 'N/A',  # Update if available
                'profession': 'N/A',  # Update if available
                'marital_status': 'N/A',  # Update if available
                'lifestyle': {}  # Update if available
            }

            # Get AI response
            ai_response_text = generate_response(data['message'], cached_data)
            if ai_response_text.startswith("Error:"):
                logging.error(f"AI response error: {ai_response_text}")
                return jsonify({'message': ai_response_text}), 500

            # Append AI response
            ai_message = {
                'id': f"msg-{len(conversation.messages) + 2}",
                'text': ai_response_text,
                'isUser': False,
                'timestamp': datetime.datetime.now().strftime('%I:%M %p')
            }
            conversation.messages.append(ai_message)

            db.session.commit()
            logging.debug(f"Updated messages: {conversation.messages}")
            return jsonify({'messages': conversation.messages}), 200
        except Exception as e:
            db.session.rollback()
            logging.error(f"Error saving conversation: {str(e)}")
            return jsonify({'message': f'Error saving conversation: {str(e)}'}), 500

@auth_bp.route('/conversation/new', methods=['POST'])
def new_conversation():
    logging.debug("Received new conversation request")
    auth_header = request.headers.get('Authorization')
    if not auth_header or not auth_header.startswith('Bearer '):
        logging.error("Missing or invalid token")
        return jsonify({'message': 'Missing or invalid token'}), 401

    token = auth_header.split(' ')[1]
    try:
        data = jwt.decode(token, current_app.config['SECRET_KEY'], algorithms=['HS256'], options={'verify_exp': True})
        user = User.query.get(data['user_id'])
        if not user:
            logging.error("User not found")
            return jsonify({'message': 'User not found'}), 404
    except jwt.ExpiredSignatureError as e:
        logging.error(f"Token expired: {str(e)}")
        return jsonify({'message': 'Token has expired'}), 401
    except jwt.InvalidTokenError as e:
        logging.error(f"Invalid token: {str(e)}")
        return jsonify({'message': 'Invalid token'}), 401
    except Exception as e:
        logging.error(f"Error decoding token: {str(e)}")
        return jsonify({'message': f'Error decoding token: {str(e)}'}), 500

    try:
        conversation = Conversation(user_id=user.id, messages=[])
        db.session.add(conversation)
        db.session.commit()
        logging.debug("New conversation created")
        return jsonify({'messages': [], 'message': 'New conversation started'}), 201
    except Exception as e:
        db.session.rollback()
        logging.error(f"Error creating new conversation: {str(e)}")
        return jsonify({'message': f'Error creating new conversation: {str(e)}'}), 500

@auth_bp.route('/test', methods=['GET'])
def test():
    logging.debug("Test route accessed")
    return jsonify({'message': 'Test route working'}), 200