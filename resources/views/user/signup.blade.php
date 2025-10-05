@include('partials.header')
<div class="flex flex-col h-screen bg-black">

    <div class="mt-auto mb-40 md:mb-0 ml-1">
        <form action="/signup" method="post" class="flex flex-col">
            @csrf
            <div class="dos">Welcome new user.</div>
            <div class="dos">Enter the username and the password you wish to use.</div>
            <div class="flex">
                <label for="user" class="dos">Username:</label><input id="user" type="text" name="username"
                                                                      class="dos flex-grow value="{{old('username')}}">
            </div>
            @error('username')
            <div class="dos red">Err: {{$message}}</div>
            @enderror

            <div class="flex">
                <label for="password" class="dos">Password:</label><input id="password" type="password" name="password"
                                                                          class="dos flex-grow"
                                                                          value="{{old('password')}}">
            </div>
            @error('password')
            <div class="dos red">Err: {{$message}}</div>
            @enderror
            <div class="flex">
                <label for="password_confirmation" class="dos">Password confirmation:</label><input
                    id="password_confirmation" type="password" name="password_confirmation"
                    class="dos flex-grow">
            </div>

            <a href="/signin" class="dos url mr-auto">Already have an account? Log in here</a>

            <button type="submit" class="flex dos url hover:cursor-pointer md:hidden mr-auto">Submit!</button>



        </form>
    </div>
</div>

@include('scripts.formScript')


@include('partials.footer')
