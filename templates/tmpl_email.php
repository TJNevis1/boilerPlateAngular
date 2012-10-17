<div class="prereg">
    <h1>You’re Almost Finished</h1>
    <h4>We need a little more info so we can send you your prize if you win!</h4>
    <form action="">
        <input type="text" value="" id="reg_first_name" placeholder="First Name"/>
        <input type="text" value="" id="reg_last_name" placeholder="Last Name"/>
        <input type="text" value="" id="email" placeholder="Email"/>
        <input type="text" value="" id="email_confirm" placeholder="Confirm Email"/>
        <div id="age_check" class="checkblock">
            <div class="checkbox"></div>
            I am at least 18 years old.
        </div>
        <div id="agree" class="checkblock">
            <div class="checkbox"></div>
            I agree to the <a href="#">Official Rules</a> and the <a href="#">Privacy Policy</a>.
        </div>
        <button class="btn btn_submit" id="submit_email" ng-click="submitEmail()">Submit</button>
    </form>
    <div class="error"><b>Error:</b> Please correct the highlighted fields</div>
</div>