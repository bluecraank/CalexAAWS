<h1>Räume</h1>

<a href="/rooms/create">Neuer Raum</a>

<table border="1">
<tr>
    <th>Name</th>
    <th>Username</th>
    <th>Kapazität</th>
</tr>

@foreach($rooms as $room)
<tr>
    <td>{{ $room->name }}</td>
    <td>{{ $room->username }}</td>
    <td>{{ $room->capacity }}</td>
</tr>
@endforeach
</table>