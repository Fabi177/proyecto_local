<?php

use App\Support\Rubros;

test('hay 19 categorías principales más "Otros", cada una con su ícono', function () {
    $categorias = Rubros::categorias();

    expect($categorias)->toHaveCount(20)
        ->and(array_keys($categorias))->toBe([
            'Gastronomía', 'Compras', 'Automotores', 'Hogar y construcción', 'Tecnología', 'Salud',
            'Belleza', 'Mascotas', 'Servicios', 'Profesionales', 'Agro', 'Transporte', 'Turismo',
            'Entretenimiento', 'Educación', 'Finanzas', 'Inmobiliaria', 'Combustibles', 'Instituciones', 'Otros',
        ]);

    foreach ($categorias as $nombre => $categoria) {
        expect($categoria['icono'])->not->toBe('', "{$nombre} sin ícono")
            ->and($categoria['items'])->not->toBeEmpty("{$nombre} sin rubros");
    }

    expect($categorias['Automotores']['icono'])->toBe('🚗')
        ->and($categorias['Automotores']['items'])->toHaveKeys(['Mecanico', 'Gomeria', 'Lubricentro', 'ChapaPintura', 'Concesionaria']);
});

test('ninguna clave de rubro se repite entre categorías y todas son simples', function () {
    $total = array_sum(array_map(fn ($c) => count($c['items']), Rubros::categorias()));

    // Si una clave estuviera en dos categorías, la lista plana tendría menos elementos que la suma.
    expect(Rubros::planos())->toHaveCount($total);

    foreach (Rubros::claves() as $clave) {
        expect($clave)->toMatch('/^[A-Za-z]+$/', "La clave \"{$clave}\" tiene tildes, espacios o símbolos");
    }

    foreach (Rubros::planos() as $clave => $etiqueta) {
        expect(trim($etiqueta))->not->toBe('', "La clave {$clave} no tiene etiqueta");
    }
});

test('las claves que ya usaban los comercios y la portada siguen existiendo', function () {
    // Claves del desplegable viejo que se mantienen y las que enlaza la portada (?rubro=...).
    $siguen = [
        'Restaurante', 'Cafe', 'Panaderia', 'Supermercado', 'Verduleria', 'Carniceria', 'Delivery',
        'Indumentaria', 'Calzado', 'Libreria', 'Jugueteria', 'Ferreteria', 'Kiosco', 'Farmacia', 'Optica',
        'Gimnasio', 'Peluqueria', 'Estetica', 'Mecanico', 'Lavanderia', 'Hogar', 'Entretenimiento', 'Otro',
    ];

    foreach ($siguen as $clave) {
        expect(Rubros::claves())->toContain($clave);
    }
});

test('la etiqueta de una clave se muestra y, si no existe, se devuelve el texto tal cual', function () {
    expect(Rubros::etiqueta('Gomeria'))->toBe('Gomería')
        ->and(Rubros::etiqueta('Panadería de la esquina'))->toBe('Panadería de la esquina')
        ->and(Rubros::etiquetas(['Cafe', 'BarPub', 'algo viejo']))->toBe(['Cafetería', 'Bar / Pub', 'algo viejo']);
});

test('la clave se reconoce sin importar mayúsculas ni tildes', function () {
    expect(Rubros::canonica('cafe'))->toBe('Cafe')
        ->and(Rubros::canonica('  FARMACIA '))->toBe('Farmacia')
        ->and(Rubros::canonica('Óptica'))->toBe('Optica')
        ->and(Rubros::canonica(' algo raro '))->toBe('algo raro');
});

test('buscar por texto encuentra los rubros por su nombre, sin tildes ni mayúsculas', function () {
    expect(Rubros::clavesQueCoinciden('pizz'))->toBe(['Pizzeria'])
        ->and(Rubros::clavesQueCoinciden('GOMERIA'))->toBe(['Gomeria'])
        ->and(Rubros::clavesQueCoinciden('gomería'))->toBe(['Gomeria'])
        ->and(Rubros::clavesQueCoinciden('xyzxyz'))->toBe([])
        ->and(Rubros::clavesQueCoinciden('   '))->toBe([]);
});

test('clavesPorCategoria lista las claves de cada categoría', function () {
    $porCategoria = Rubros::clavesPorCategoria();

    expect($porCategoria)->toHaveCount(20)
        ->and($porCategoria['Combustibles'])->toBe(['EstacionServicio', 'GNC', 'Combustibles', 'Lubricantes']);
});
