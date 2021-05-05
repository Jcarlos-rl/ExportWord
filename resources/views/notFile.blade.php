<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row vh-100">
            <div class="col-sm-6 d-flex align-items-center">
                <img class="img-fluid" src="{{ asset('images/notfile.svg')}}" width="500" alt="">
            </div>
            <div class="col-sm-6 d-flex align-items-center">
                <div>
                    <span class="badge rounded-pill bg-warning text-dark">Archivo no encontrado</span>
                    <h2 class="mt-3 inconsolata">OH NO! ERROR 404</h2>
                    <p>Lo sentimos, no pudimos acceder al recurso JSON requerido para la creación del documento Word.</p>
                    <p class="my-4">Posibles errores:</p>
                    <ul>
                        <li>Dirección de archivo erronea.</li>
                        <li>Archivo protegido.</li>
                    </ul>
                    <p>Ubicación proporcionada del archivo JSON: {{$path_file}}</p>
                    <a href="{{$path_file}}" target="_blank" class="btn btn-outline-primary">Verificar dirección del archivo</a>
                    <a href="" class="btn btn-outline-primary">Volver al sistema</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
