<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <table class="w-full">
        <tr>
            <td class="w-half">
                <img width="55" height="55" src="https://img.icons8.com/external-kiranshastry-gradient-kiranshastry/100/external-file-interface-kiranshastry-gradient-kiranshastry.png" alt="external-file-interface-kiranshastry-gradient-kiranshastry"/>            
                <h4 style="margin-top: 10px;">Transaccion: {{$detailDataFormat['transaction_type']}}</h4>
                <div style="margin-top: 10px;">Estado: {{$detailDataFormat['status']}}</div>
                <div style="margin-top: 10px;">Tienda: {{$detailDataFormat['warehouse_name']}}</div>
            </td>
            <td class="w-half">
                <h2>Acuse ID: {{$detailDataFormat['acknowledgment_key']}}</h2>
                <div>Fecha de acuse: {{$detailDataFormat['created_at']}}</div>
            </td>
        </tr> 
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>Tienda:</h4></div>
                    <div>{{$detailDataFormat['warehouse_name']}} (punto de venta)</div>
                </td>
            </tr>
        </table>
    </div>
 
    <div class="margin-top">
        <h3>Combos vendidos</h3>
        <table class="products">
            <tr>
                <th>Nombre combo</th>
                <th>Nombre tienda</th>
                <th>Precio venta</th>
                <th>Cantidad vendidos</th>
                <th>Total</th>
            </tr>
            <tr class="items">
                @foreach($comboSalesSummary as $item)
                    <tr class="items">
                        <td>
                            {{ $item['combo_name'] }}
                        </td>
                        <td>
                            {{ $item['warehouse_name'] }}
                        </td>
                        <td>
                            ${{ $item['sale_price'] }}
                        </td>
                        <td>
                            {{ $item['quantity_sold'] }}
                        </td>
                        <td>
                            ${{ $item['total'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>

    @if (count($comboSalesSummary) == 0)
        <div class="margin-top">
            <h4>No hay combos vendidos</h4>
        </div>
    @endif
 
    <div class="total">
        <span style="font-weight: bold">Total:</span> ${{$detailDataFormat['total_amount']}}
    </div>

    <div class="margin-top-two">
        <table class="signatures" style="margin: 0 auto; width: 40%;">
            <tr>
                <td class="w-half">
                    <hr>
                    <div>{{$detailDataFormat['manager_name']}}</div>
                </td>
            </tr>
        </table>
    </div>
 
    <div class="footer margin-top-two">
        <div>Gracias!</div>
        <div>&copy; Halcones de Xalapa</div>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h4 {
            margin: 0;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }
        .w-half-two{
            width: 25%;
        }
        .margin-top {
            margin-top: 1.25rem;
        }
        .margin-top-two {
            margin-top: 7rem;
        }
        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        table.products {
            font-size: 0.875rem;
        }
        table.products tr {
            background-color: rgb(15, 204, 221);
        }
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }
        table tr.items {
            background-color: rgb(241 245 249);
        }
        table tr.items td {
            padding: 0.5rem;
        }
        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 1rem;
        }
        .signatures div {
            text-align: center;
        }
        .signatures hr {
            border: none;
            border-top: 1px solid black;
        }
    </style>
    
</body>
</html>