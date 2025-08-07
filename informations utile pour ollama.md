The Ollama Laravel package provides seamless integration with the Ollama API:

Ollama-Laravel is a Laravel package that provides a seamless integration with the Ollama API. It includes functionalities for model management, prompt generation, format setting, and more. This package is perfect for developers looking to leverage the power of the Ollama API in their Laravel applications.

Access the powerful Meta LLaMA: A foundational, 65-billion-parameter language model locally and interface with it using Laravel. You can access various models such as llama2, openchat, starcoder (code generation model trained on 80+ languages), sqlcoder, and other models trained in medical, psychology, and more. This is an excellent way for developers to get experience with large language learning models locally!

Once you install this package in Laravel, you can use the package's Ollama facade to interact with the model of your choice. Here's an example of a typical exchange with a language model:

use Cloudstudio\Ollama\Facades\Ollama;
 
$response = Ollama::prompt('How do I create a route in Laravel 10?')
    ->model('llama2')
    ->options(['temperature' => 0.8])
    ->stream(false)
    ->ask();
Here's an example response from a tinker shell I tried with it:
> use Cloudstudio\0llama\Facades\ollama;
> Ollama: :prompt('How do I create a route in Laravel 10?')->model('llama2')->options(['temperature' => 0.8])->stream(false)->ask()

"model" => "11ama2",
"created_at" => "2023-11-30T04:08:39.596537Z",
"response" => """
\n
In Laravel 10, you can create a route using the `Route' class in the `app/Http/Routes.php' file. Here's an example of how to
create a simple route:\n

1. Open the 'app/Http/Routes.php' file in your Laravel project.\n
2. Add a new route by creating a new method in the 'Route' class, like this:\n

Route: :get('/', function () {n
return view('welcome');\n

This route will handle any request to the root URL ('/`) and will display the 'welcome' view.\n
3. You can also use routes with parameters, like this:\n

Route: :get('/users/{user}', function ($user) {\n
return 'User ID: ' . $user;\n
3);\n

This route will handle any request to the URL `/users/<user_id>' and will pass the `user_id' as a parameter to the method.\n
4. You can also use nested routes, like this:\n
...
Route: : group([], function () {\n
Route: :get('/users', function () {n
return view('users');\n

Route: :get('/users/{user]', function ($user) {n
return 'User ID: ' . $user; \n

This route will handle any request to the URL `/users' or `/users/<user_id>' and will pass the `user_id' as a parameter to t
he method. \n
5. You can also use routes with middleware, like this:\n

Route: :middleware(['auth'])->get('/dashboard', function () n
return view('dashboard");\n
3);\n

This route will only be accessible if the user is authenticated.\n
6. You can also use routes with conditions, like this:\n

Route: :get('/users/{user}', function ($user) {\n
return 'User ID: ' . $user;\n



