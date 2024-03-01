<?php
/*
 Template Name: Napthe
 */
 ?>
<?php get_header(); ?>
<?php
if(isset($_POST['naptien']))
{
    $loaithe    = $_POST['telco'];
    $seri       = $_POST['serial'];
    $mathe      = $_POST['code'];
    $sotien     = $_POST['amount'];
    if(!$mathe || !$seri || !$loaithe || !$sotien)
    {
        $tt = 'Vui lòng nhập đầy đủ thông tin';
    }
    if(strlen($seri) < 5)
    {
        $tt = 'Seri không đúng định dạng';
    }
    if(strlen($mathe) < 5)
    {
        $tt = 'Mã không đúng định dạng';
    }
    else 
    {
        $request_id = rand(100000000, 999999999);  //Mã đơn hàng của bạn
        $command = 'charging';  // Nap the
        $url = 'https://gachthe1s.com/chargingws/v2';
        $partner_id = '9920044461';
        $partner_key = '814ea2b8e450192a4a2e2e987be2fc95';

        $dataPost = array();
        $dataPost['request_id'] = $request_id;
        $dataPost['code'] = $_POST['code'];
        $dataPost['partner_id'] = $partner_id;
        $dataPost['serial'] = $_POST['serial'];
        $dataPost['telco'] = $_POST['telco'];
        $dataPost['command'] = $command;
        ksort($dataPost);
        $sign = $partner_key;
        foreach ($dataPost as $item) {
            $sign .= $item;
        }
        
        $mysign = md5($sign);

        $dataPost['amount'] = $_POST['amount'];
        $dataPost['sign'] = $mysign;

        $data = http_build_query($dataPost);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($result, true);
        if($obj['status'] == 99)
        {
            $tt = 'Đã nạp thẻ thành công';
        }
        else
        {
            $tt = $obj['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nạp thẻ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="row" style="margin-top: 50px;">
        <div class="col-md-8" style="float:none;margin:0 auto;">
            <form method="POST">
                <div class="form-group">
                    <label>Loại thẻ:</label>
                    <select class="form-control" name="telco">
                        <option value="">Chọn loại thẻ</option>
                        <option value="VIETTEL">Viettel</option>
                        <option value="MOBIFONE">Mobifone</option>
                        <option value="VINAPHONE">Vinaphone</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mệnh giá:</label>
                    <select class="form-control" name="amount">
                        <option value="">Chọn mệnh giá</option>
                        <option value="10000">10.000</option>
                        <option value="20000">20.000</option>
                        <option value="30000">30.000</option>
                        <option value="50000">50.000</option>
                        <option value="100000">100.000</option>
                        <option value="200000">200.000</option>
                        <option value="300000">300.000</option>
                        <option value="500000">500.000</option>
                        <option value="1000000">1.000.000</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Số seri:</label>
                    <input type="text" class="form-control" name="serial"/>
                </div>
                <div class="form-group">
                    <label>Mã thẻ:</label>
                    <input type="text" class="form-control" name="code"/>
                </div>
                <?php if(isset($tt))
                {
                    echo $tt;
                }
                ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block" name="naptien">NẠP NGAY</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
        integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T"
        crossorigin="anonymous"></script>
</body>
</html>
<?php get_footer(); ?>