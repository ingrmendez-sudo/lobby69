import contextlib

from django.apps import AppConfig
from django.utils.translation import gettext_lazy as _


class UsersConfig(AppConfig):
    name = "social.users"
    label = "social_users"  # ← AGREGAR ESTA LÍNEA
    verbose_name = _("Users")
    default_auto_field = 'django.db.models.BigAutoField'

    def ready(self):
        with contextlib.suppress(ImportError):
            import social.users.signals  # noqa: F401
