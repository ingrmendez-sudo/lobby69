"""
FILE: social/services/supabase_service.py
VERSION: 1.0.0
DESCRIPCIÓN: Servicio centralizado para operaciones con Supabase
LAST UPDATED: 2026-06-15

FUNCIONALIDADES:
- Lectura de perfiles
- Actualización de perfiles
- Caché de perfiles
- Manejo de errores
"""

import os
import logging
from supabase import create_client, Client
from django.core.cache import cache
from typing import Optional, Dict, Any

logger = logging.getLogger(__name__)

class SupabaseService:
    """Servicio centralizado para Supabase"""

    def __init__(self):
        self.url = os.environ.get('SUPABASE_URL')
        self.key = os.environ.get('SUPABASE_KEY')
        self.client: Client = create_client(self.url, self.key)

    def get_profile(self, user_id: int, use_cache: bool = True) -> Optional[Dict]:
        """
        Obtener perfil de usuario con caché opcional

        Args:
            user_id: ID del usuario
            use_cache: Usar caché (por defecto True)

        Returns:
            Dict con datos del perfil o None
        """
        cache_key = f"profile:{user_id}"

        # Intentar obtener del caché
        if use_cache:
            cached_profile = cache.get(cache_key)
            if cached_profile:
                logger.debug(f"Perfil {user_id} obtenido del caché")
                return cached_profile

        try:
            response = self.client.table('profiles')\
                .select('*')\
                .eq('user_id', user_id)\
                .single()\
                .execute()

            profile = response.data

            # Guardar en caché por 1 hora
            cache.set(cache_key, profile, 3600)
            logger.info(f"Perfil {user_id} obtenido de Supabase")

            return profile

        except Exception as e:
            logger.error(f"Error obteniendo perfil {user_id}: {str(e)}")
            return None

    def update_profile(self, user_id: int, data: Dict) -> bool:
        """
        Actualizar perfil de usuario

        Args:
            user_id: ID del usuario
            data: Datos a actualizar

        Returns:
            True si se actualizó exitosamente
        """
        try:
            # Validar datos antes de actualizar
            validated_data = self._validate_profile_data(data)

            response = self.client.table('profiles')\
                .update(validated_data)\
                .eq('user_id', user_id)\
                .execute()

            # Limpiar caché
            cache.delete(f"profile:{user_id}")

            logger.info(f"Perfil {user_id} actualizado exitosamente")
            return True

        except Exception as e:
            logger.error(f"Error actualizando perfil {user_id}: {str(e)}")
            return False

    def _validate_profile_data(self, data: Dict) -> Dict:
        """Validar y sanitizar datos del perfil"""
        allowed_fields = {
            'display_name', 'bio', 'age', 'gender',
            'city', 'state', 'country',
            'interests', 'looking_for', 'privacy_level'
        }

        # Filtrar solo campos permitidos
        validated = {k: v for k, v in data.items() if k in allowed_fields}

        # Validaciones específicas
        if 'age' in validated:
            age = int(validated['age'])
            if age < 18 or age > 120:
                raise ValueError("Edad inválida")
            validated['age'] = age

        if 'display_name' in validated:
            name = validated['display_name'].strip()
            if len(name) < 2 or len(name) > 100:
                raise ValueError("Nombre inválido")
            validated['display_name'] = name

        if 'privacy_level' in validated:
            if validated['privacy_level'] not in ['public', 'friends', 'private']:
                raise ValueError("Nivel de privacidad inválido")

        return validated

    def get_notifications(self, user_id: int, limit: int = 10) -> list:
        """Obtener notificaciones recientes"""
        try:
            response = self.client.table('notifications')\
                .select('*')\
                .eq('user_id', user_id)\
                .order('created_at', desc=True)\
                .limit(limit)\
                .execute()

            return response.data

        except Exception as e:
            logger.error(f"Error obteniendo notificaciones: {str(e)}")
            return []

    def search_profiles(self, query: str, limit: int = 20) -> list:
        """Buscar perfiles por nombre o ciudad"""
        try:
            response = self.client.table('profiles')\
                .select('id, display_name, avatar_url, city, age')\
                .or_(f"display_name.ilike.%{query}%,city.ilike.%{query}%")\
                .limit(limit)\
                .execute()

            return response.data

        except Exception as e:
            logger.error(f"Error buscando perfiles: {str(e)}")
            return []


# Instancia global
supabase_service = SupabaseService()
