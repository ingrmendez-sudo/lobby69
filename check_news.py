import os
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'config.settings.local')
import django
django.setup()

from django.conf import settings
from supabase import create_client

# Conectar a Supabase
supabase = create_client(settings.SUPABASE_URL, settings.SUPABASE_KEY)

# Listar noticias
try:
    response = supabase.table('news').select('id, title, category, published, created_at').order('created_at', desc=True).execute()
    print(f"\n✅ Total noticias en Supabase: {len(response.data)}\n")
    for news in response.data:
        print(f"  📰 {news['title']}")
        print(f"     Categoría: {news['category']}")
        print(f"     Publicado: {'✅ Sí' if news['published'] else '❌ No (Borrador)'}")
        print(f"     Fecha: {news['created_at']}\n")
except Exception as e:
    print(f"❌ Error: {e}")
    import traceback
    traceback.print_exc()
