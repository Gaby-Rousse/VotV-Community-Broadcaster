@include('partials.header')
<div class="flex flex-col h-screen bg-black">
    <div class="mt-auto  mb-40 md:mb-0 ml-1">
        <form action="/signin" method="post" class="flex flex-col">
            @csrf
            @if(Session('error'))
                <div class="dos red">{{Session('error')}}</div>
            @endif
            <div class="dos">{{Session('newUser') ? Session('newUser') : 'Welcome. Please authenticate'}}</div>
            <div class="flex">
                <label for="user" class="dos">Username:</label><input id="user" type="text" name="username"
                                                                      class="dos flex-grow">
            </div>
            @error('username')
            <div class="dos red">Err: {{$message}}</div>
            @enderror
            <div class="flex">
                <label for="password" class="dos">Password:</label><input id="password" type="password" name="password"
                                                                          class="dos flex-grow">
            </div>
            @error('password')
            <div class="dos red">Err: {{$message}}</div>
            @enderror

            <div class="flex">
                <label for="rememberMe" class="dos">Remember me</label>
                <input id="rememberMe" type="checkbox" name="rememberMe" class="dos ml-2 rounded-none hover:cursor-pointer">
            </div>


            <a href="/signup" class="dos url mr-auto">Need an account? Register here</a>

            <button type="submit" class="dos url hover:cursor-pointer mr-auto md:hidden">Submit!</button>

        </form>
    </div>
</div>

@include('scripts.formScript')

@include('partials.footer')
