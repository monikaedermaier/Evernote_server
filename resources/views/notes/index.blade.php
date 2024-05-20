<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>
    </head>
    <body>
        <ul>
           @foreach($notes as $note)
                <li><a href="notes/{{$note->id}}">{{$note->id}} {{$note->title}} {{$note->description}}</a></li>
           @endforeach
        </ul>
    </body>
</html>
