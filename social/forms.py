"""
FILE: social/forms.py
VERSION: 1.0.0
DESCRIPCIÓN: Formularios para el perfil de usuario
"""

from django import forms
from django.core.exceptions import ValidationError


class ProfileEditForm(forms.Form):
    """
    Formulario para editar perfil

    CAMPOS:
    - display_name: Nombre a mostrar
    - bio: Biografía
    - age: Edad
    - gender: Género
    - city, state, country: Ubicación
    - interests: Intereses
    - looking_for: Qué busca
    - privacy_level: Nivel de privacidad
    """

    GENDER_CHOICES = [
        ('male', 'Hombre'),
        ('female', 'Mujer'),
        ('non_binary', 'No binario'),
        ('prefer_not', 'Prefiero no especificar'),
    ]

    PRIVACY_CHOICES = [
        ('public', 'Público'),
        ('friends', 'Solo conexiones'),
        ('private', 'Privado'),
    ]

    display_name = forms.CharField(
        max_length=100,
        min_length=2,
        required=True,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'Nombre a mostrar',
            'aria-label': 'Nombre a mostrar'
        })
    )

    bio = forms.CharField(
        max_length=500,
        required=False,
        widget=forms.Textarea(attrs={
            'class': 'form-textarea',
            'rows': 4,
            'placeholder': 'Cuéntanos sobre ti...',
            'aria-label': 'Biografía'
        })
    )

    age = forms.IntegerField(
        min_value=18,
        max_value=120,
        required=True,
        widget=forms.NumberInput(attrs={
            'class': 'form-input',
            'placeholder': 'Edad',
            'aria-label': 'Edad'
        })
    )

    gender = forms.ChoiceField(
        choices=GENDER_CHOICES,
        required=True,
        widget=forms.RadioSelect(attrs={
            'class': 'form-radio'
        })
    )

    city = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'Ciudad',
            'aria-label': 'Ciudad'
        })
    )

    state = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'Estado/Provincia',
            'aria-label': 'Estado'
        })
    )

    country = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'País',
            'aria-label': 'País'
        })
    )

    interests = forms.CharField(
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'Intereses (separados por comas)',
            'aria-label': 'Intereses'
        })
    )

    looking_for = forms.CharField(
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-input',
            'placeholder': 'Qué buscas (separado por comas)',
            'aria-label': 'Qué buscas'
        })
    )

    privacy_level = forms.ChoiceField(
        choices=PRIVACY_CHOICES,
        required=True,
        widget=forms.Select(attrs={
            'class': 'form-select',
            'aria-label': 'Nivel de privacidad'
        })
    )

    # VALIDACIONES
    def clean_display_name(self):
        name = self.cleaned_data.get('display_name', '').strip()
        if not name:
            raise ValidationError('El nombre no puede estar vacío')
        if len(name) < 2:
            raise ValidationError('El nombre debe tener al menos 2 caracteres')
        return name

    def clean_age(self):
        age = self.cleaned_data.get('age')
        if age and (age < 18 or age > 120):
            raise ValidationError('La edad debe estar entre 18 y 120 años')
        return age

    def clean_bio(self):
        bio = self.cleaned_data.get('bio', '')
        if len(bio) > 500:
            raise ValidationError('La biografía no puede exceder 500 caracteres')
        return bio
