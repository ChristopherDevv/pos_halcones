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
                <h4 style="margin-top: 10px;">Transacion: Corte de caja</h4>
                <div style="margin-top: 10px;">Estado: completado</div>
                <div style="margin-top: 10px;">Caja: {{$cashRegisterData['cash_register_type']}}</div>
            </td>
            <td class="w-half">
                <h2>Acuse ID: {{$acknowledgmentKey}}</h2>
                <div style="margin-top: 0px;">Fecha de apertura: {{$cashRegisterData['opening_time']}} </div>
                <div style="margin-top: 0px;">Fecha de cierre: {{$cashRegisterData['closing_time']}}</div>
                <div style="margin-top: 0px;">Vendedor apertura: {{$cashRegisterData['opening_cashier']}}</div>
                <div style="margin-top: 0px;">Vendedor cierre: {{$cashRegisterData['closing_cashier']}}</div>
                <h4 style="margin-top: 0px;">Saldo de apertura: ${{$cashRegisterData['opening_balance']}}</h4>
                <h4 style="margin-top: 0px;">Saldo en cierre (bruto): ${{$cashRegisterData['closing_balance']}}</h4>
                <?php 
                    $balanceNeto = $cashRegisterData['closing_balance'] - $cashRegisterData['opening_balance'];
                ?>
                <h4 style="margin-top: 0px;">Saldo en cierre (neto): ${{$balanceNeto}}</h4>
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
        <h3>Productos vendidos individualmente</h3>
        <table class="products">
            <tr>
                <th>Nombre</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Precio venta</th>
                <th>Cantidad vendida</th>
                <th>Por combo</th>
                <th>Total</th>
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
                            ${{ $item['price'] }}
                        </td>
                        <td>
                            {{ $item['quantity'] }}
                        </td>
                        <td>
                            {{ $item['is_combo_sale'] }}
                        </td>
                        <td>
                            ${{ $item['total_amount'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>
    <?php
        $totalAmountProductSummary = 0;
        foreach($productSummary as $item){
            $totalAmountProductSummary += $item['total_amount'];
        }
    ?>

    <div class="total">
        <span style="font-weight: bold">Total:</span> ${{$totalAmountProductSummary}}
    </div>

    @if(count($productSummary) == 0)
        <div class="margin-top">
            <h4>No hay productos vendidos</h4>
        </div>
    @endif

    <div class="margin-top-two">
{{--         <h3>Productos vendidos en combos</h3>
        <table class="products">
            <tr>
                <th>Combo</th>
                <th>Producto</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Precio</th>
                <th>Cantidad vendida</th>
                <th>Total</th>
            </tr>
            <tr class="items">
                @foreach($productsSoldInCombos as $item)
                    <tr class="items">
                        <td>
                            {{ $item['combo_name'] }}
                        </td>
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
                            ${{ $item['price'] }}
                        </td>
                        <td>
                            {{ $item['quantity'] }}
                        </td>
                        <td>
                            ${{ $item['total_amount'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="total">
        <span style="font-weight: bold">Total:</span> ${{$totalAmountProductsSoldInCombos}}
    </div>

    @if(count($productsSoldInCombos) == 0)
        <div class="margin-top">
            <h4>No hay productos vendidos en combos</h4>
        </div>
    @endif --}}

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

    <?php
    $totalAmountcomboSalesSummary = 0;
    foreach($comboSalesSummary as $item){
        $totalAmountcomboSalesSummary += $item['total'];
    }
?>

<div class="total">
    <span style="font-weight: bold">Total:</span> ${{$totalAmountcomboSalesSummary}}
</div>

    @if(count($comboSalesSummary) == 0)
        <div class="margin-top">
            <h4>No hay combos vendidos</h4>
        </div>
    @endif

    <div class="margin-top">
        <h3>Productos por cortesia</h3>
        <table class="products">
            <tr>
                <th>Nombre</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Precio venta</th>
                <th>Cantidad vendida</th>
                <th>Por combo</th>
                <th>Total</th>
            </tr>
            <tr class="items">
                @foreach($productSummaryCourtesies as $item)
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
                        <td>
                            {{ $item['is_combo_sale'] }}
                        </td>
                        <td>
                            ${{ $item['total_amount'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>

    @if(count($productSummaryCourtesies) == 0)
        <div class="margin-top">
            <h4>No se dieron productos por cortesia</h4>
        </div>
    @endif

    <div class="margin-top">
        <h3>Productos por cobrar</h3>
        <table class="products">
            <tr>
                <th>Usuario</th>
                <th>Producto</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Precio venta</th>
                <th>Cantidad vendida</th>
                <th>Total</th>
            </tr>
            <tr class="items">
                @foreach($productSummaryOnCredit as $item)
                    <tr class="items">
                        <td>
                            {{ $item['debtor_full_name'] }}
                        </td>
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
                        <td>
                            ${{ $item['total_amount'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>

    @if(count($productSummaryCourtesies) == 0)
        <div class="margin-top">
            <h4>No se dieron productos por cobrar</h4>
        </div>
    @endif

    <div class="margin-top">
        <h3>Productos vendidos para vendedores ambulantes</h3>
        <table class="products">
            <tr>
                <th>Nombre</th>
                <th>Cantidad UM</th>
                <th>Valor UM</th>
                <th>Talla</th>
                <th>Categoria</th>
                <th>Precio venta</th>
                <th>Cantidad vendida</th>
                <th>Total</th>
            </tr>
            <tr class="items">
                @foreach($productSummaryForBucketVendors as $item)
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
                        <td>
                            ${{ $item['total_amount'] }}
                        </td>
                    </tr>
                @endforeach
            </tr>
        </table>
    </div>
    <?php
        $totalAmountProductSummaryBucket = 0;
        foreach($productSummaryForBucketVendors as $item){
            $totalAmountProductSummaryBucket += $item['total_amount'];
        }
    ?>

    <div class="total">
        <span style="font-weight: bold">Total:</span> ${{$totalAmountProductSummaryBucket}}
    </div>

    @if(count($productSummaryForBucketVendors) == 0)
        <div class="margin-top">
            <h4>No hay productos vendidos</h4>
        </div>
    @endif
 
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