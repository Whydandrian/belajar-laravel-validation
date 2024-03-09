<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Login Form</title>
</head>
<body>
  @if ($errors->any())
    <ul>
      @foreach ($errors->all() as $error)
        <li>
          {{$error}}
        </li>
      @endforeach
    </ul>
  @endif

  <form action="/form" method="post">
    @csrf
    <label>Username : <input type="text" name="username"></label><br>
    <label>Password : <input type="password" name="username"></label><br>
    <input type="submit" value="Login">
  </form>

</body>
</html>