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
                <h4 style="margin-top: 10px;">Transaccion: Resumen de venta</h4>
                <div style="margin-top: 10px;">Estado: completado</div>
                <div style="margin-top: 10px;">Tienda: {{$cashRegisterData['pos_product_warehouse_name']}}</div>
                <div style="margin-top: 10px;">
                    <h4>Nota:</h4> Se toma en cuenta todos los  
                    <br>
                    productos vendidos en venta particular y por combo 
                </div>
            </td>
            <td class="w-half">
                <h2>Acuse ID: {{$acknowledgmentKey}}</h2>
                <div style="margin-top: 0px;">Hora de apertura: {{$cashRegisterData['opening_time']}} </div>
                <div style="margin-top: 0px;">Hora de cierre: {{$cashRegisterData['closing_time']}}</div>
                <div style="margin-top: 0px;">Saldo de apertura: ${{$cashRegisterData['opening_balance']}}</div>
                <div style="margin-top: 0px;">Saldo en cierre (bruto): ${{$cashRegisterData['closing_balance']}}</div>
                <?php 
                    $balanceNeto = $cashRegisterData['closing_balance'] - $cashRegisterData['opening_balance'];
                ?>
                <div style="margin-top: 0px;">Saldo en cierre (neto): ${{$balanceNeto}}</div>
                <div style="margin-top: 0px;">Manager de tienda: {{$cashRegisterData['opening_manager']}}</div>
                <h4 style="margin-top: 0px;">{{$cashRegisterData['total_transactions_card']}} transacciones con tarjeta: ${{$cashRegisterData['total_card_amount']}} </h4>
                <h4 style="margin-top: 0px;">{{$cashRegisterData['total_transactions_cash']}} transacciones con efectivo: ${{$cashRegisterData['total_cash_amount']}} </h4>


            </td>
        </tr> 
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>Tienda:</h4></div>
                    <div> {{$cashRegisterData['pos_product_warehouse_name']}} (punto de venta)</div>
                    <div></div>
                </td>
            </tr>
        </table>
    </div>
 
    <div class="margin-top">
        <h3>Productos vendidos</h3>
        <table class="products">
            <tr>
                <th>Nombre</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Cantidad vendida</th>
            </tr>
            <tr class="items">
                @foreach($productSummary as $item)
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
                    <div>{{$userManagerName}}</div>
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