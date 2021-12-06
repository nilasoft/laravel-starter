<script>
    $(document).ready(function () {

        var counter = 0;
        var c = 0;
        var increament = 0;
        var i = setInterval(function () {
            $(".loading-page .counter h1").html(c + "%");
            $(".loading-page .counter hr").css("width", c + "%");

            increament = between(1, 10);
            counter += increament;
            c += increament;

            if (counter >= 101) {
                clearInterval(i);
                window.location.href = "{{ $redirectToGateway->getTargetUrl() }}";
            }
        }, 50);

        function between(min, max) {
            return Math.floor(
                Math.random() * (max - min + 1) + min
            )
        }
    });
</script>
