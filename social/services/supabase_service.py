from supabase import create_client, Client
from django.conf import settings


class SupabaseService:
    _instance = None

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(SupabaseService, cls).__new__(cls)
            cls._instance.client = create_client(
                settings.SUPABASE_URL,
                settings.SUPABASE_KEY
            )
        return cls._instance

    def get_client(self) -> Client:
        return self.client

    def get_profile(self, profile_id: str):
        """Obtener perfil por ID"""
        try:
            response = self.client.table('profiles').select('*').eq('id', profile_id).execute()
            if response.data and len(response.data) > 0:
                return response.data[0]
            return None
        except Exception as e:
            print(f"Error obteniendo perfil: {e}")
            return None

    def get_profile_by_account(self, account_id: str):
        """Obtener perfil por account_id (UUID del usuario)"""
        try:
            print(f"[DEBUG] Buscando perfil para account_id: {account_id}")
            response = self.client.table('profiles').select('*').eq('account_id', account_id).execute()

            if response.data and len(response.data) > 0:
                print(f"[DEBUG] Perfil encontrado: {response.data[0]}")
                return response.data[0]

            print(f"[DEBUG] No se encontró perfil para account_id: {account_id}")
            return None
        except Exception as e:
            print(f"Error obteniendo perfil por account: {e}")
            import traceback
            traceback.print_exc()
            return None

    def create_profile(self, data: dict):
        """Crear un nuevo perfil"""
        try:
            print(f"[DEBUG] Creando perfil con datos: {data}")
            resp = self.client.table('profiles').insert(data).execute()
            print(f"[DEBUG] Response de Supabase: {resp}")
            if resp.data:
                print(f"[DEBUG] Perfil creado exitosamente: {resp.data}")
                return resp.data[0] if isinstance(resp.data, list) else resp.data
            print("[DEBUG] Error: respuesta vacía al crear perfil")
            return None
        except Exception as e:
            print(f"[ERROR] Error creando perfil: {e}")
            import traceback
            traceback.print_exc()
            return None

    def update_profile(self, account_id: str, data: dict):
        """Actualizar perfil existente por account_id"""
        try:
            print(f"[DEBUG] Actualizando perfil para account_id: {account_id}")
            print(f"[DEBUG] Datos a actualizar: {data}")
            data_to_update = {k: v for k, v in data.items() if k != 'account_id'}
            resp = self.client.table('profiles').update(data_to_update).eq('account_id', account_id).execute()
            print(f"[DEBUG] Response de Supabase: {resp}")
            if resp.data:
                print(f"[DEBUG] Perfil actualizado exitosamente: {resp.data}")
                return resp.data[0] if isinstance(resp.data, list) else resp.data
            print("[DEBUG] Error: respuesta vacía al actualizar perfil")
            return None
        except Exception as e:
            print(f"[ERROR] Error actualizando perfil: {e}")
            import traceback
            traceback.print_exc()
            return None

    def get_profile_persons(self, profile_id: str):
        """Obtener personas relacionadas al perfil"""
        try:
            response = self.client.table('profile_persons').select('*').eq('profile_id', profile_id).execute()
            return response.data if response.data else []
        except Exception as e:
            print(f"Error obteniendo personas del perfil: {e}")
            return []

    def get_media_items(self, profile_id: str, limit: int = 20):
        """Obtener items de galería"""
        try:
            response = self.client.table('media_items').select('*').eq('profile_id', profile_id).limit(limit).execute()
            return response.data if response.data else []
        except Exception as e:
            print(f"Error obteniendo galería: {e}")
            return []

    def get_profile_interests(self, profile_id: str):
        """Obtener intereses del perfil"""
        try:
            response = self.client.table('profile_interest_types').select('*, interest_types(*)').eq('profile_id', profile_id).execute()
            return response.data if response.data else []
        except Exception as e:
            print(f"Error obteniendo intereses: {e}")
            return []


supabase_service = SupabaseService()
