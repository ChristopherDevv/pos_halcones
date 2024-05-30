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
                <h4 style="margin-top: 10px;">Transaccion: {{$detailDataFormat['type']}}</h4>
                <div style="margin-top: 10px;">Estado: {{$detailDataFormat['status']}}</div>
                <div style="margin-top: 10px;">Almacen: {{$detailDataFormat['warehouse']}}</div>
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
                    <div><h4>Desde:</h4></div>
                    <div>{{$detailDataFormat['warehouse']}} (almacen global)</div>
                    <div>{{$managerDataFormat['sender_phone']}}</div>
                </td>
                <td class="w-half">
                    <div><h4>Para:</h4></div>
                    <div>{{$managerDataFormat['receiver_name']}}</div>
                    <div>{{$managerDataFormat['receiver_phone']}}</div>
                </td>
            </tr>
        </table>
    </div>
 
    <div class="margin-top">
        <h3>Productos transferidos</h3>
        <table class="products">
            <tr>
                <th>Nombre</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Precio de venta</th>
                <th>Stock</th>
            </tr>
            <tr class="items">
                @foreach($productDataFormat as $item)
                    <tr class="items">
                        <td>
                            {{ $item['name'] }}
                        </td>
                        <td>
                            {{ $item['unit_measurement_quantity'] }}
                        </td>
                        <td>
                            {{ $item['unit_measurement_abbr'] }}
                        </td>
                        <td>
                            {{ $item['clothing_size'] }}
                        </td>
                        <td>
                            {{ $item['clothing_category'] }}
                        </td>
                        <td>
                            ${{ $item['price'] }}
                        </td>
                        <td>
                            {{ $item['quantity'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>
 
    <div class="margin-top-two">
        <table class="signatures" style="margin: 0 auto; width: 40%;">
            <tr>
                <td class="w-half">
                    <hr>
                    <div>{{$managerDataFormat['sender_name']}}</div>
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