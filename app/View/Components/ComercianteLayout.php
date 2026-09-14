<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ComercianteLayout extends Component
{
    /**
     * Get the view / contents that represent the component.
     * * ESTE ES EL CAMBIO CLAVE:
     * Le decimos que renderice nuestro archivo en 'layouts'
     * en lugar del que está en 'components'.
     */
    public function render(): View
    {
        return view('layouts.comerciante-layout');
    }
}
