"""
Local settings for LOBBY69 project.
"""

from .base import *  # noqa
import environ

env = environ.Env()

DEBUG = True
ALLOWED_HOSTS = ["*"]

# Supabase Configuration
SUPABASE_URL = "https://kjhaquimghhejqznleyn.supabase.co"
SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtqaGFxdWltZ2hoZWpxem5sZXluIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTEyMDI4MywiZXhwIjoyMDkwNjk2MjgzfQ.0TB7MkfeQl3rMF-c0sLL4wuxfOBGTarvbu_g9bvbqMk"  # ← PEGA LA service_role secret AQUI
SUPABASE_ANON_KEY = "sb_publishable_fcmWTRMK4rZ1WN7OlrNXnQ_5F7fqiGF"  # Esta es la anon (opcional)

# DATABASES - SQLite para desarrollo local
DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.sqlite3",
        "NAME": BASE_DIR / "db.sqlite3",
    }
}

# SECURITY
SECRET_KEY = env("DJANGO_SECRET_KEY", default="local-development-secret-key")

# LOGGING
LOGGING = {
    "version": 1,
    "disable_existing_loggers": False,
    "formatters": {
        "verbose": {
            "format": "%(levelname)s %(asctime)s %(module)s %(process)d %(thread)d %(message)s",
        },
    },
    "handlers": {
        "console": {
            "level": "DEBUG",
            "class": "logging.StreamHandler",
            "formatter": "verbose",
        },
    },
    "root": {"level": "INFO", "handlers": ["console"]},
}

# EMAIL
EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'
EMAIL_HOST = 'smtp.gmail.com'
EMAIL_PORT = 587
EMAIL_USE_TLS = True
EMAIL_HOST_USER = 'ivonneytony2@gmail.com'
EMAIL_HOST_PASSWORD = 'ch0rr1t0s'
DEFAULT_FROM_EMAIL = 'admin@lobby69.com'

# ALLAUTH
ACCOUNT_EMAIL_VERIFICATION = "none"
LOGIN_REDIRECT_URL = "pages:dashboard"

from pathlib import Path

PROJECT_DIR = Path(__file__).resolve().parent.parent.parent  # C:\web\Lobby69\lobby69

STATIC_URL = '/static/'
STATICFILES_DIRS = [
    PROJECT_DIR / 'static',
]
STATIC_ROOT = PROJECT_DIR / 'staticfiles'
