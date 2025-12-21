@include('partials.header')
<div class="flex flex-col h-screen bg-black">
    <div class="mt-auto  mb-40 md:mb-0 ml-1">
        <form action="/account" method="post" class="flex flex-col">
            @csrf

            @error('username')
            <div class="dos red">Err: {{$message}}</div>
            @enderror

            <div class="dos">Hello {{Auth::user()->username}}.</div>
            <div class="dos">You can adjust your account settings here.</div>
            <div class="dos">1) Log off</div>
            <div class="dos">2) Change username</div>
            <div class="dos">3) Delete account (and all uploaded data.)</div>
            <div class="flex">
                <label for="option" class="dos">Please choose an option:</label><input id="option" type="text"
                                                                                       name="choice"
                                                                                       class="dos flex-grow">
            </div>

            <button type="submit" class="dos url hover:cursor-pointer mr-auto md:hidden">Submit!</button>
        </form>
    </div>
</div>

@include('scripts.formScript')

<script>
    $(() => {
        $('form').submit(function (e) {
            let choice = $("#option").val()
            if (choice === '2') {
                e.preventDefault();
                $("#option").val('')
                $('button').before(`<div class="flex flex-row"><div class="dos">Specify the new username: </div> <input id="confirm" type="text"
                                                                                       name="username"
                                                                                       class="dos flex-grow"></div>`);
                $('#confirm').focus();
            }
            if (choice === '3') {
                e.preventDefault();
                $("#option").val('')
                $('button').before(`<div class="flex flex-row"><div class="dos">Please confirm you want to delete your account (y/n): </div> <input id="confirm" type="text"
                                                                                       name="confirm"
                                                                                       class="dos flex-grow"></div>`);
                $('#confirm').focus();
            }
        });
    });
</script>

@include('partials.footer')
