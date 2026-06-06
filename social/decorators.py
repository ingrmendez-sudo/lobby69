from functools import wraps
from django.shortcuts import redirect
from django.contrib import messages

def admin_required(function):
    """
    Decorador que verifica:
    1. Usuario está autenticado
    2. Usuario es staff
    3. Redirige si no cumple requisitos
    """
    @wraps(function)
    def wrapper(request, *args, **kwargs):
        if not request.user.is_authenticated:
            messages.warning(request, 'Debes iniciar sesión para acceder al admin')
            return redirect(f"pages:login?next={request.path}")
        
        if not request.user.is_staff:
            messages.error(request, 'No tienes permisos de administrador')
            return redirect('pages:home_feed')
        
        return function(request, *args, **kwargs)
    
    return wrapper
