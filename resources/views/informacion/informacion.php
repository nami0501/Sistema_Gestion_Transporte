<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .aviso-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            font-family: 'Arial', sans-serif;
        }
        
        .aviso-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .aviso-icon {
            width: 36px;
            height: 36px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #3498db;
            border-radius: 50%;
            color: white;
            font-size: 20px;
        }
        
        .aviso-title {
            color: #2c3e50;
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .aviso-content {
            color: #34495e;
            line-height: 1.6;
        }
        
        .aviso-importante {
            background-color: #f8f9fa;
            border-left: 4px solid #e74c3c;
            padding: 12px 15px;
            margin: 15px 0;
        }
        
        .aviso-footer {
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
            text-align: right;
        }
        
        @media (max-width: 600px) {
            .aviso-container {
                margin: 10px;
                padding: 15px;
            }
            
            .aviso-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="aviso-container">
        <div class="aviso-header">
            <div class="aviso-icon">
                <i>!</i>
            </div>
            <h2 class="aviso-title">Información Importante de Transporte</h2>
        </div>
        
        <div class="aviso-content">
            <p>Estimados usuarios, nos complace informarles sobre las actualizaciones en nuestros servicios de transporte:</p>
            
            <ul>
                <li>A partir del 15 de abril, se incrementará la frecuencia de salidas en las rutas principales.</li>
                <li>Se ha habilitado un nuevo punto de atención al cliente en la terminal central.</li>
                <li>La aplicación móvil ahora permite consultar el estado de las rutas en tiempo real.</li>
            </ul>
            
            <div class="aviso-importante">
                <strong>¡Atención!</strong> Durante el fin de semana del 20 al 22 de abril, se realizarán trabajos de mantenimiento en la autopista norte. Por favor consulte rutas alternativas.
            </div>
            
            <p>Para más información sobre horarios y servicios específicos, por favor consulte la sección de "Servicios" o comuníquese con nuestro centro de atención al cliente.</p>
        </div>
        
        <div class="aviso-footer">
            <p>Última actualización: 8 de abril, 2025</p>
        </div>
    </div>
</body>
</html>