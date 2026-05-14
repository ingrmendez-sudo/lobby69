from django import forms

class ProfileUpdateForm(forms.Form):
    display_name = forms.CharField(
        max_length=255,
        required=True,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Nombre para mostrar'
        })
    )
    bio = forms.CharField(
        max_length=500,
        required=False,
        widget=forms.Textarea(attrs={
            'class': 'form-control',
            'placeholder': 'Cuéntanos sobre ti',
            'rows': 4
        })
    )
    age = forms.IntegerField(
        required=False,
        widget=forms.NumberInput(attrs={
            'class': 'form-control',
            'min': 18,
            'max': 120
        })
    )
    gender = forms.ChoiceField(
        choices=[
            ('', 'Selecciona tu género'),
            ('masculino', 'Masculino'),
            ('femenino', 'Femenino'),
            ('otro', 'Otro'),
            ('prefiero_no_decir', 'Prefiero no decir')
        ],
        required=False,
        widget=forms.Select(attrs={'class': 'form-control'})
    )
    city = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Ciudad'
        })
    )
    state = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Estado/Provincia'
        })
    )
    country = forms.CharField(
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'País'
        })
    )
    profile_type = forms.ChoiceField(
        choices=[
            ('individual', 'Individual'),
            ('pareja', 'Pareja'),
            ('grupo', 'Grupo')
        ],
        required=False,
        widget=forms.Select(attrs={'class': 'form-control'})
    )
    relationship_status = forms.ChoiceField(
        choices=[
            ('', 'Selecciona tu estado'),
            ('soltero', 'Soltero/a'),
            ('en_relacion', 'En relación'),
            ('casado', 'Casado/a'),
            ('divorciado', 'Divorciado/a'),
            ('viudo', 'Viudo/a')
        ],
        required=False,
        widget=forms.Select(attrs={'class': 'form-control'})
    )
