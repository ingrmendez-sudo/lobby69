"""
FILE: social/tests/test_navbar.py
VERSION: 1.0.0
DESCRIPCIÓN: Tests para navbar premium
"""

from django.test import TestCase, Client
from django.contrib.auth.models import User
from django.urls import reverse


class NavbarTestCase(TestCase):
    """Tests para el navbar premium"""

    def setUp(self):
        self.client = Client()
        self.user = User.objects.create_user(
            username='testuser',
            email='test@lobby69.com',
            password='testpass123'
        )

    def test_navbar_requires_authentication(self):
        """Navbar requiere autenticación"""
        response = self.client.get(reverse('dashboard'))
        self.assertEqual(response.status_code, 302)

    def test_navbar_renders_authenticated(self):
        """Navbar se renderiza cuando autenticado"""
        self.client.login(username='testuser', password='testpass123')
        response = self.client.get(reverse('dashboard'))
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'navbar-premium')

    def test_navbar_elements(self):
        """Verificar elementos del navbar"""
        self.client.login(username='testuser', password='testpass123')
        response = self.client.get(reverse('dashboard'))

        self.assertContains(response, 'LOBBY69')
        self.assertContains(response, 'Inicio')
        self.assertContains(response, 'Explorar')
        self.assertContains(response, 'Mensajes')

    def test_navbar_dropdowns(self):
        """Verificar dropdowns"""
        self.client.login(username='testuser', password='testpass123')
        response = self.client.get(reverse('dashboard'))

        self.assertContains(response, 'notification-menu')
        self.assertContains(response, 'privacy-menu')
        self.assertContains(response, 'user-menu')

    def test_logout_link(self):
        """Verificar link de logout"""
        self.client.login(username='testuser', password='testpass123')
        response = self.client.get(reverse('dashboard'))

        self.assertContains(response, 'Cerrar Sesión')
