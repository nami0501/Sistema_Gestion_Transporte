<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class LogoController extends Controller
{
    public function generateLogo()
    {
        // Crear una imagen de 200x200 píxeles
        $img = Image::canvas(200, 200, '#007bff');

        // Agregar texto
        $img->text('SGT', 100, 100, function($font) {
            $font->file(public_path('fonts/arial.ttf'));
            $font->size(60);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });

        // Guardar la imagen
        $img->save(public_path('images/logo.png'));

        return "Logo generado correctamente.";
    }
}