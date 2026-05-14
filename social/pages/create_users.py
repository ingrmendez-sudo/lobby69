from django.contrib.auth import get_user_model

User = get_user_model()

# Crear usuario de prueba 1
user1 = User.objects.create_user(
    username='juan_perez',
    email='juan@example.com',
    password='password123',
    first_name='Juan',
    last_name='Pérez'
)

# Crear usuario de prueba 2
user2 = User.objects.create_user(
    username='maria_garcia',
    email='maria@example.com',
    password='password123',
    first_name='María',
    last_name='García'
)

# Crear usuario de prueba 3
user3 = User.objects.create_user(
    username='carlos_lopez',
    email='carlos@example.com',
    password='password123',
    first_name='Carlos',
    last_name='López'
)

print("✅ Usuarios creados exitosamente")
print(f"✅ {user1.username} - {user1.email}")
print(f"✅ {user2.username} - {user2.email}")
print(f"✅ {user3.username} - {user3.email}")
