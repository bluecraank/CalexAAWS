<h1>Raum erstellen</h1>

<form method="POST" action="/rooms">
@csrf

<div>
Name
<input name="name">
</div>

<div>
Username
<input name="username">
</div>

<div>
Password
<input name="password" type="password">
</div>

<div>
Personanzahl
<input name="capacity" type="number">
</div>

<button type="submit">Speichern</button>

</form>