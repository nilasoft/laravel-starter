<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    @include('invoices.default.components.styles')
</head>
<body>
<div class="container">
    <div class="invoice">
        <header>
            <section>
                <h1>Invoice</h1>
                <span>{{ $model->created_at->toDateString() }}</span>
            </section>

            <section>
                <span>{{ random_int(11111,99999) }}</span>
            </section>
        </header>

        <main>
            <section>
                <span>Product</span>
                <span>Unit</span>
                <span>Price</span>
            </section>

            <section>
                <figure>
                    <span><strong>Espresso</strong> (large)</span>
                    <span>1</span>
                    <span>2.90</span>
                </figure>

                <figure>
                    <span><strong>Cappuccino</strong> (small)</span>
                    <span>2</span>
                    <span>7.00</span>
                </figure>
            </section>

            <section>
                <span>Total</span>
                <span>9.90</span>
            </section>
        </main>

        <footer>
            <a href="#0"></a>
            <a href="#0">Back to home</a>
        </footer>
    </div>
</div>

</body>
</html>
