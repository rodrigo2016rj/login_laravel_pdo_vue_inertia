<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="{{asset('css/padroes_entre_visuais.css')}}" type="text/css" rel="stylesheet"/>
    <title inertia>{{config('app.name', 'Laravel')}}</title>
    @vite('resources/js/app.js')
    @inertiaHead
  </head>
  <body>
    @inertia
  </body>
</html>
