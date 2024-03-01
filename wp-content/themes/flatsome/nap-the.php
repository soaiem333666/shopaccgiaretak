<?php
/*
 Template Name: Napthe
 */
get_header(); ?>
<?php
if (isset($_POST['naptien'])) {
    $loaithe    = $_POST['telco'];
    $seri       = $_POST['serial'];
    $mathe      = $_POST['code'];
    $sotien     = $_POST['amount'];
    if (!$mathe || !$seri || !$loaithe || !$sotien) {
        $tt = '<div class="error-msg">Vui lòng nhập đầy đủ thông tin</div>';
    }
    if (strlen($seri) < 5) {
        $tt = '<div class="error-msg">Seri không đúng định dạng</div>';
    }
    if (strlen($mathe) < 5) {
        $tt = '<div class="error-msg">Mã không đúng định dạng</div>';
    } else {
        $request_id = rand(100000000, 999999999);  //Mã đơn hàng của bạn
        $command = 'charging';  // Nap the
        $url = 'https://gachthe1s.com/chargingws/v2';
        $partner_id = '28623469364';
        $partner_key = 'c328386ea1525af1832a8c8553d399ff';

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
        if ($obj['status'] == 99) {
            $tt = '<div class="success-msg">Đã nạp thẻ thành công, tiền sẽ được cộng trong vòng 5-10 phút</div>';
        } else {
            $tt = '<div class="error-msg">Thẻ đã tồn tại</div>';
        }
    }
}
?>
<!-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nạp thẻ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
</head>
<body> -->
<div class="warning-mess-wrapper">
    <div class="warning-mess"><label>Lưu ý:</label>
        <p>- Hiện website hỗ trợ 3 loại thẻ cào Viettel, Mobifone, Vinaphone</p>
        <p>- Nhớ chọn đúng phần loại thẻ và mệnh giá thẻ, nhằm giúp chúng tôi xác minh <span>phòng tránh trường hợp mất thẻ</span></p>
    </div>
</div>
<div class="mobile-card">
    <div class="mobile-card-all">
        <div class="mobile-card-wrapper">
            <div class="panel-heading">
                Nạp Thẻ
            </div>
            <div class="mobile-card-form">
                <form method="POST">
					<?php
if ( is_user_logged_in() ) {
  $current_user = wp_get_current_user();
  $user_email = $current_user->user_email;
  echo '<input type="hidden" name="your-email" value="' . esc_attr( $user_email ) . '">';
}
?>
					<?php if (isset($tt)) {
		echo $tt;
	}
	?>
	<div style="display: none">
		<input type="hidden" name="_wpcf7" value="2004" />
		<input type="hidden" name="_wpcf7_version" value="5.6.2" />
		<input type="hidden" name="_wpcf7_locale" value="vi" />
		<input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f2004-o3" />
		<input type="hidden" name="_wpcf7_container_post" value="0" />
		<input type="hidden" name="_wpcf7_posted_data_hash" value="" />
	</div>
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
		<label>Mã thẻ:</label>
		<input type="text" class="form-control" name="code" />
	</div>
	<div class="form-group">
		<label>Số seri:</label>
		<input type="text" class="form-control" name="serial" />
	</div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-block" name="naptien">NẠP NGAY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
        integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T"
        crossorigin="anonymous"></script> -->
<!-- </body>
</html> -->

<?php get_footer(); ?>