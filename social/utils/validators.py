import re
from django.core.exceptions import ValidationError

def validate_comment(comment_text):
    """
    Valida que el comentario NO contenga:
    - Direcciones de email
    - Números de teléfono
    - URLs / links
    - Redes sociales
    """

    # Patrón para emails
    email_pattern = r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}'
    if re.search(email_pattern, comment_text):
        raise ValidationError('No se permiten direcciones de correo electrónico en comentarios.')

    # Patrón para teléfonos (números de 7-15 dígitos consecutivos)
    phone_pattern = r'\b\d{7,15}\b'
    if re.search(phone_pattern, comment_text):
        raise ValidationError('No se permiten números de teléfono en comentarios.')

    # Patrón para URLs / links
    url_pattern = r'https?://|www\.|\.com|\.net|\.org|\.io|\.co'
    if re.search(url_pattern, comment_text, re.IGNORECASE):
        raise ValidationError('No se permiten enlaces en comentarios.')

    # Patrón para redes sociales comunes
    social_pattern = r'@|#|instagram|facebook|twitter|whatsapp|telegram|tiktok'
    if re.search(social_pattern, comment_text, re.IGNORECASE):
        raise ValidationError('No se permiten referencias a redes sociales en comentarios.')

    return True
