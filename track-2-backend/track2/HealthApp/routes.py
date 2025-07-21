from flask import Blueprint, request, jsonify, current_app
import jwt
import datetime
from . import db, bcrypt
from .models import User, Conversation
from .ai_engine import generate_personalized_response
import logging
import uuid

logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

auth_bp = Blueprint('auth', __name__, url_prefix='/api/auth')

def verify_user_from_token(auth_header):
    logger.debug(f"Verifying token with auth_header: {auth_header}")
    if not auth_header or not auth_header.startswith('Bearer '):
        logger.error("Missing or invalid Authorization header")
        return None
    token = auth_header.split(' ')[1]
    try:
        decoded = jwt.decode(token, current_app.config['SECRET_KEY'], algorithms=['HS256'], options={'verify_exp': True})
        user = User.query.get(decoded['user_id'])
        if not user:
            logger.error("User not found for decoded user_id")
            return None
        logger.debug(f"User verified: {user.id}")
        return user
    except jwt.ExpiredSignatureError:
        logger.error("Token expired")
        return None
    except jwt.InvalidTokenError:
        logger.error("Invalid token")
        return None
    except Exception as e:
        logger.error(f"Error verifying token: {str(e)}")
        return None

@auth_bp.route('/signup', methods=['POST'])
def signup():
    logger.debug("Received signup request")
    try:
        data = request.get_json()
    except Exception as e:
        logger.error(f"Invalid JSON input: {str(e)}")
        return jsonify({'message': 'Invalid JSON format'}), 400
    logger.debug(f"Request data: {data}")
    required_fields = ['name', 'username', 'email', 'password', 'phone', 'language', 'gender']
    
    if not all(field in data for field in required_fields):
        logger.error("Missing required fields")
        return jsonify({'message': 'Missing required fields'}), 400

    if len(data['name']) < 2 or len(data['name']) > 120:
        logger.error("Invalid name length")
        return jsonify({'message': 'Name must be 2-120 characters'}), 400
    if len(data['username']) < 3 or len(data['username']) > 20:
        logger.error("Invalid username length")
        return jsonify({'message': 'Username must be 3-20 characters'}), 400
    if not data['username'].replace('_', '').replace('-', '').isalnum():
        logger.error("Invalid username characters")
        return jsonify({'message': 'Username can only contain letters, numbers, underscores, or hyphens'}), 400
    if User.query.filter_by(username=data['username']).first():
        logger.error("Username already exists")
        return jsonify({'message': 'Username already exists'}), 400
    if User.query.filter_by(email=data['email']).first():
        logger.error("Email already exists")
        return jsonify({'message': 'Email already exists'}), 400
    if not data['email'] or not '@' in data['email'] or len(data['email']) > 120:
        logger.error("Invalid email format")
        return jsonify({'message': 'Invalid email format'}), 400
    if len(data['password']) < 8:
        logger.error("Password too short")
        return jsonify({'message': 'Password must be at least 8 characters'}), 400
    if len(data['phone']) > 15:
        logger.error("Phone number too long")
        return jsonify({'message': 'Phone number cannot exceed 15 characters'}), 400
    if User.query.filter_by(phone=data['phone']).first():
        logger.error("Phone number already exists")
        return jsonify({'message': 'Phone number already exists'}), 400
    if len(data['language']) > 32:
        logger.error("Language too long")
        return jsonify({'message': 'Language cannot exceed 32 characters'}), 400
    if data['gender'] not in ['Male', 'Female', 'Other']:
        logger.error("Invalid gender selection")
        return jsonify({'message': 'Invalid gender selection'}), 400

    try:
        hashed_password = bcrypt.generate_password_hash(data['password']).decode('utf-8')
        logger.debug("Password hashed")
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
        logger.debug("User created successfully")
        token = jwt.encode(
            {
                'user_id': new_user.id,
                'exp': datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(hours=24)
            },
            current_app.config['SECRET_KEY'],
            algorithm='HS256'
        )
        logger.debug(f"JWT token generated: {token}")
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
        logger.error(f"Error creating user: {str(e)}")
        return jsonify({'message': f'Error creating user: {str(e)}'}), 500

@auth_bp.route('/signin', methods=['POST'])
def signin():
    logger.debug("Received signin request")
    try:
        data = request.get_json()
    except Exception as e:
        logger.error(f"Invalid JSON input: {str(e)}")
        return jsonify({'message': 'Invalid JSON format'}), 400
    logger.debug(f"Request data: {data}")
    if not data or 'email' not in data or 'password' not in data:
        logger.error("Email and password are required")
        return jsonify({'message': 'Email and password are required'}), 400

    try:
        user = User.query.filter_by(email=data['email']).first()
        logger.debug(f"User query result: {user}")
        if not user or not bcrypt.check_password_hash(user.password, data['password']):
            logger.error("Invalid credentials")
            return jsonify({'message': 'Invalid credentials'}), 401

        token = jwt.encode(
            {
                'user_id': user.id,
                'exp': datetime.datetime.now(datetime.timezone.utc) + datetime.timedelta(hours=24)
            },
            current_app.config['SECRET_KEY'],
            algorithm='HS256'
        )
        logger.debug(f"JWT token generated: {token}")
        return jsonify({
            'token': token,
            'user': {
                'id': user.id,
                'username': user.username,
                'email': user.email
            }
        }), 200
    except Exception as e:
        logger.error(f"Error during signin: {str(e)}")
        return jsonify({'message': f'Error during signin: {str(e)}'}), 500

@auth_bp.route('/verify', methods=['GET'])
def verify():
    logger.debug("Received verify request")
    user = verify_user_from_token(request.headers.get('Authorization'))
    if not user:
        logger.error("Invalid or missing token")
        return jsonify({'message': 'Invalid or missing token'}), 401
    logger.debug("User verified successfully")
    return jsonify({
        'user': {
            'id': user.id,
            'username': user.username,
            'email': user.email
        }
    }), 200

@auth_bp.route('/conversations', methods=['GET'])
def conversations():
    logger.debug("Received conversations request")
    user = verify_user_from_token(request.headers.get('Authorization'))
    if not user:
        logger.error("Invalid or missing token")
        return jsonify({'message': 'Invalid or missing token'}), 401

    try:
        page = int(request.args.get('page', 1))
        per_page = int(request.args.get('per_page', 10))
        conversations = Conversation.query.filter_by(user_id=user.id)\
            .order_by(Conversation.updated_at.desc().nullslast(), Conversation.created_at.desc())\
            .paginate(page=page, per_page=per_page, error_out=False)
        
        response = {
            'conversations': [{
                'id': conv.id,
                'created_at': conv.created_at.isoformat(),
                'updated_at': conv.updated_at.isoformat() if conv.updated_at else conv.created_at.isoformat(),
                'preview': conv.messages[0]['text'][:100] if conv.messages and len(conv.messages) > 0 else 'No messages yet',
                'message_count': len(conv.messages) if conv.messages else 0
            } for conv in conversations.items],
            'total': conversations.total,
            'pages': conversations.pages,
            'current_page': page
        }
        logger.debug(f"Fetched {len(conversations.items)} conversations for page {page}")
        return jsonify(response), 200
    except Exception as e:
        logger.error(f"Error fetching conversations: {str(e)}")
        return jsonify({'message': f'Error fetching conversations: {str(e)}'}), 500

@auth_bp.route('/conversation/<int:conversation_id>', methods=['GET', 'POST'])
def conversation(conversation_id):
    logger.debug(f"Received conversation request for ID: {conversation_id}")
    user = verify_user_from_token(request.headers.get('Authorization'))
    if not user:
        logger.error("Invalid or missing token")
        return jsonify({'message': 'Invalid or missing token'}), 401

    conversation = Conversation.query.filter_by(id=conversation_id, user_id=user.id).first()
    if not conversation:
        logger.error(f"Conversation {conversation_id} not found for user {user.id}")
        return jsonify({'message': 'Conversation not found'}), 404

    if request.method == 'GET':
        try:
            logger.debug(f"Fetched conversation: {conversation.id}")
            return jsonify({
                'id': conversation.id,
                'created_at': conversation.created_at.isoformat(),
                'updated_at': conversation.updated_at.isoformat() if conversation.updated_at else conversation.created_at.isoformat(),
                'messages': conversation.messages or [],
                'preview': conversation.messages[0]['text'][:100] if conversation.messages and len(conversation.messages) > 0 else 'No messages yet'
            }), 200
        except Exception as e:
            logger.error(f"Error fetching conversation: {str(e)}")
            return jsonify({'message': f'Error fetching conversation: {str(e)}'}), 500

    elif request.method == 'POST':
        try:
            data = request.get_json()
            logger.debug(f"Conversation data: {data}")
            if not data or 'message' not in data:
                logger.error("Message is required")
                return jsonify({'message': 'Message is required'}), 400

            if conversation.messages is None:
                conversation.messages = []

            session_id = f"user_{user.id}_conv_{conversation.id}"
            user_message = {
                'id': str(uuid.uuid4()),
                'text': data['message'],
                'isUser': True,
                'timestamp': datetime.datetime.now().isoformat()
            }
            conversation.messages.append(user_message)
            logger.debug(f"Added user message: {user_message}")

            patient_info = {
                'name': user.name,
                'language': user.language,
                'gender': user.gender,
                'age': getattr(user, 'age', 'N/A'),
                'chronic_conditions': getattr(user, 'chronic_conditions', 'None'),
                'allergies': getattr(user, 'allergies', 'None'),
                'region': getattr(user, 'region', 'N/A'),
                'city': getattr(user, 'city', 'N/A'),
                'profession': getattr(user, 'profession', 'N/A'),
                'marital_status': getattr(user, 'marital_status', 'N/A'),
                'lifestyle': getattr(user, 'lifestyle', {})
            }

            ai_response_text = generate_personalized_response(data['message'], patient_info, session_id)
            logger.debug(f"AI response received: {ai_response_text}")

            if ai_response_text.startswith("Error:"):
                logger.error(f"AI response error: {ai_response_text}")
                return jsonify({'message': ai_response_text}), 500

            ai_message = {
                'id': str(uuid.uuid4()),
                'text': ai_response_text,
                'isUser': False,
                'timestamp': datetime.datetime.now().isoformat()
            }
            conversation.messages.append(ai_message)
            logger.debug(f"Added AI message: {ai_message}")

            conversation.updated_at = datetime.datetime.now()
            from sqlalchemy.orm.attributes import flag_modified
            flag_modified(conversation, "messages")
            db.session.commit()
            logger.debug(f"Final message count after commit: {len(conversation.messages)}")
            return jsonify({
                'id': conversation.id,
                'created_at': conversation.created_at.isoformat(),
                'updated_at': conversation.updated_at.isoformat(),
                'messages': conversation.messages,
                'preview': user_message['text'][:100]
            }), 200

        except Exception as e:
            db.session.rollback()
            logger.error(f"Error saving conversation: {str(e)}")
            return jsonify({'message': f'Error saving conversation: {str(e)}'}), 500

@auth_bp.route('/conversation', methods=['GET', 'POST'])
def latest_conversation():
    logger.debug("Received latest conversation request")
    user = verify_user_from_token(request.headers.get('Authorization'))
    if not user:
        logger.error("Invalid or missing token")
        return jsonify({'message': 'Invalid or missing token'}), 401

    conversation = Conversation.query.filter_by(user_id=user.id).order_by(Conversation.updated_at.desc().nullslast(), Conversation.created_at.desc()).first()
    if not conversation:
        logger.debug("No conversation found, creating new one")
        conversation = Conversation(user_id=user.id, messages=[])
        db.session.add(conversation)
        db.session.commit()

    if request.method == 'GET':
        try:
            logger.debug(f"Fetched conversation: {conversation.id}")
            return jsonify({
                'id': conversation.id,
                'created_at': conversation.created_at.isoformat(),
                'updated_at': conversation.updated_at.isoformat() if conversation.updated_at else conversation.created_at.isoformat(),
                'messages': conversation.messages or [],
                'preview': conversation.messages[0]['text'][:100] if conversation.messages and len(conversation.messages) > 0 else 'No messages yet'
            }), 200
        except Exception as e:
            logger.error(f"Error fetching conversation: {str(e)}")
            return jsonify({'message': f'Error fetching conversation: {str(e)}'}), 500

    elif request.method == 'POST':
        try:
            data = request.get_json()
            logger.debug(f"Conversation data: {data}")
            if not data or 'message' not in data:
                logger.error("Message is required")
                return jsonify({'message': 'Message is required'}), 400

            if conversation.messages is None:
                conversation.messages = []

            session_id = f"user_{user.id}_conv_{conversation.id}"
            user_message = {
                'id': str(uuid.uuid4()),
                'text': data['message'],
                'isUser': True,
                'timestamp': datetime.datetime.now().isoformat()
            }
            conversation.messages.append(user_message)
            logger.debug(f"Added user message: {user_message}")

            patient_info = {
                'name': user.name,
                'language': user.language,
                'gender': user.gender,
                'age': getattr(user, 'age', 'N/A'),
                'chronic_conditions': getattr(user, 'chronic_conditions', 'None'),
                'allergies': getattr(user, 'allergies', 'None'),
                'region': getattr(user, 'region', 'N/A'),
                'city': getattr(user, 'city', 'N/A'),
                'profession': getattr(user, 'profession', 'N/A'),
                'marital_status': getattr(user, 'marital_status', 'N/A'),
                'lifestyle': getattr(user, 'lifestyle', {})
            }

            ai_response_text = generate_personalized_response(data['message'], patient_info, session_id)
            logger.debug(f"AI response received: {ai_response_text}")

            if ai_response_text.startswith("Error:"):
                logger.error(f"AI response error: {ai_response_text}")
                return jsonify({'message': ai_response_text}), 500

            ai_message = {
                'id': str(uuid.uuid4()),
                'text': ai_response_text,
                'isUser': False,
                'timestamp': datetime.datetime.now().isoformat()
            }
            conversation.messages.append(ai_message)
            logger.debug(f"Added AI message: {ai_message}")

            conversation.updated_at = datetime.datetime.now()
            from sqlalchemy.orm.attributes import flag_modified
            flag_modified(conversation, "messages")
            db.session.commit()
            logger.debug(f"Final message count after commit: {len(conversation.messages)}")
            return jsonify({
                'id': conversation.id,
                'created_at': conversation.created_at.isoformat(),
                'updated_at': conversation.updated_at.isoformat(),
                'messages': conversation.messages,
                'preview': user_message['text'][:100]
            }), 200

        except Exception as e:
            db.session.rollback()
            logger.error(f"Error saving conversation: {str(e)}")
            return jsonify({'message': f'Error saving conversation: {str(e)}'}), 500

@auth_bp.route('/conversation/new', methods=['POST'])
def new_conversation():
    logger.debug("Received new conversation request")
    user = verify_user_from_token(request.headers.get('Authorization'))
    if not user:
        logger.error("Invalid or missing token")
        return jsonify({'message': 'Invalid or missing token'}), 401

    try:
        conversation = Conversation(user_id=user.id, messages=[])
        db.session.add(conversation)
        db.session.commit()
        logger.debug(f"New conversation created with ID: {conversation.id}")
        return jsonify({
            'id': conversation.id,
            'created_at': conversation.created_at.isoformat(),
            'updated_at': conversation.created_at.isoformat(),
            'messages': [],
            'preview': 'No messages yet'
        }), 201
    except Exception as e:
        db.session.rollback()
        logger.error(f"Error creating new conversation: {str(e)}")
        return jsonify({'message': f'Error creating new conversation: {str(e)}'}), 500

@auth_bp.route('/test', methods=['GET'])
def test():
    logger.debug("Test route accessed")
    return jsonify({'message': 'Test route working'}), 200