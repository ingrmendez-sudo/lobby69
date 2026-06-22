<?php
$path = __DIR__ . '/app/Models/User.php';
$content = file_get_contents($path);

// Verificar si isAdmin existe
if (strpos($content, 'isAdmin') === false) {
    echo "isAdmin NO existe en el modelo — agregando..." . PHP_EOL;

    // Insertar antes del último }
    $lastBrace = strrpos($content, '}');
    $method = '
    /**
     * Verificar si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === \'admin\';
    }

    /**
     * Verificar si la cuenta está activa.
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

';
    $content = substr($content, 0, $lastBrace) . $method . '}';
    file_put_contents($path, $content);
    echo "Metodos agregados correctamente." . PHP_EOL;
} else {
    echo "isAdmin YA existe en el modelo." . PHP_EOL;
    
    // Mostrar la función actual
    preg_match('/public function isAdmin.*?\}/s', $content, $matches);
    if ($matches) {
        echo "Contenido actual:" . PHP_EOL;
        echo $matches[0] . PHP_EOL;
    }
}

// Verificar bytes
$bytes = array_values(unpack('C*', substr(file_get_contents($path), 0, 3)));
echo "Bytes: {$bytes[0]} {$bytes[1]} {$bytes[2]}" . PHP_EOL;
