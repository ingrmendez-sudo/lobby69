from django import forms

class NewsForm(forms.Form):
    title = forms.CharField(
        label='Título',
        max_length=200,
        required=True,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Ingresa el título del artículo'
        })
    )
    category = forms.CharField(
        label='Categoría',
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': 'Ej: Tutorial, Noticia, Guía'
        })
    )
    content = forms.CharField(
        label='Contenido',
        required=True,
        widget=forms.Textarea(attrs={
            'class': 'form-control',
            'rows': 10,
            'placeholder': 'Escribe el contenido del artículo'
        })
    )
    image_url = forms.URLField(
        label='URL de Imagen',
        required=False,
        widget=forms.URLInput(attrs={
            'class': 'form-control',
            'placeholder': 'https://...'
        })
    )
    published = forms.BooleanField(
        label='Publicar inmediatamente',
        required=False,
        widget=forms.CheckboxInput(attrs={
            'class': 'form-check-input'
        })
    )
