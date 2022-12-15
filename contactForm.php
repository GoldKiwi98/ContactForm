<?php

define( "FILE_DIR", "images/test/");

// 変数の初期化
$page_flag = 0;
$clean = array();
$error = array();

// サニタイズ
if( !empty($_POST) ) {

	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
	} 
}

if( !empty($clean['btn_confirm']) ) {

	$error = validation($clean);

	// ファイルのアップロード
	if( !empty($_FILES['attachment_file']['tmp_name']) ) {

		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);

		if( $upload_res !== true ) {
			$error[] = 'ファイルのアップロードに失敗しました。';
		} else {
			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
		}
	}

	if( empty($error) ) {

		$page_flag = 1;

		// セッションの書き込み
		session_start();
		$_SESSION['page'] = true;		
	}

} elseif( !empty($clean['btn_submit']) ) {

	session_start();
	if( !empty($_SESSION['page']) && $_SESSION['page'] === true ) {

		// セッションの削除
		unset($_SESSION['page']);

		$page_flag = 2;

		// 変数とタイムゾーンを初期化
		$header = null;
		$body = null;
		$admin_body = null;
		$auto_reply_subject = null;
		$auto_reply_text = null;
		$admin_reply_subject = null;
		$admin_reply_text = null;
		date_default_timezone_set('Asia/Tokyo');
		
		//日本語の使用宣言
		mb_language("ja");
		mb_internal_encoding("UTF-8");
	
		$header = "MIME-Version: 1.0\n";
		$header = "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
		$header .= "From: GRAYCODE <noreply@gray-code.com>\n";
		$header .= "Reply-To: GRAYCODE <noreply@gray-code.com>\n";
	
		// 件名を設定
		$auto_reply_subject = 'お問い合わせありがとうございます。';
	
		// 本文を設定
		$auto_reply_text = "この度は、お問い合わせ頂き誠にありがとうございます。
	下記の内容でお問い合わせを受け付けました。\n\n";
		$auto_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
		$auto_reply_text .= "氏名：" . $clean['your_name'] . "\n";
		$auto_reply_text .= "ふりがな：" . $clean['furigana'] . "\n";
		$auto_reply_text .= "携帯電話番号：" . $clean['tel'] . "\n";
		$auto_reply_text .= "メールアドレス：" . $clean['email'] . "\n";
	
		if( $clean['gender'] === "male" ) {
			$auto_reply_text .= "性別：男性\n";
		} elseif( $clean['gender'] === "female" ) {
			$auto_reply_text .= "性別：女性\n";
		}else{
			$auto_reply_text .= "性別：その他\n";
		}
		
		if( $clean['age'] === "1" ){
			$auto_reply_text .= "年齢：〜19歳\n";
		} elseif ( $clean['age'] === "2" ){
			$auto_reply_text .= "年齢：20歳〜29歳\n";
		} elseif ( $clean['age'] === "3" ){
			$auto_reply_text .= "年齢：30歳〜39歳\n";
		} elseif ( $clean['age'] === "4" ){
			$auto_reply_text .= "年齢：40歳〜49歳\n";
		} elseif( $clean['age'] === "5" ){
			$auto_reply_text .= "年齢：50歳〜59歳\n";
		} elseif( $clean['age'] === "6" ){
			$auto_reply_text .= "年齢：60歳〜\n";
		}
	
		$auto_reply_text .= "お問い合わせ内容：" . nl2br($clean['contact']) . "\n\n";
		$auto_reply_text .= "GRAYCODE 事務局";
		
		// テキストメッセージをセット
		$body = "--__BOUNDARY__\n";
		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body .= $auto_reply_text . "\n";
		$body .= "--__BOUNDARY__\n";
	
		// ファイルを添付
		if( !empty($clean['attachment_file']) ) {
			$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Transfer-Encoding: base64\n";
			$body .= "\n";
			$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
			$body .= "--__BOUNDARY__\n";
		}
	
		// 自動返信メール送信
		mb_send_mail( $clean['email'], $auto_reply_subject, $body, $header);
	
		// 運営側へ送るメールの件名
		$admin_reply_subject = "お問い合わせを受け付けました";
	
		// 本文を設定
		$admin_reply_text = "下記の内容でお問い合わせがありました。\n\n";
		$admin_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
		$admin_reply_text .= "氏名：" . $clean['your_name'] . "\n";
		$admin_reply_text .= "ふりがな：" . $clean['furigana'] . "\n";
		$admin_reply_text .= "メールアドレス：" . $clean['email'] . "\n";
	
		if( $clean['gender'] === "male" ) {
			$admin_reply_text .= "性別：男性\n";
		} elseif( $clean['gender'] === "female") {
			$admin_reply_text .= "性別：女性\n";
		}else{
			$admin_reply_text .= "性別：その他\n";
		}
	
		if( $clean['age'] === "1" ){
			$admin_reply_text .= "年齢：〜19歳\n";
		} elseif ( $clean['age'] === "2" ){
			$admin_reply_text .= "年齢：20歳〜29歳\n";
		} elseif ( $clean['age'] === "3" ){
			$admin_reply_text .= "年齢：30歳〜39歳\n";
		} elseif ( $clean['age'] === "4" ){
			$admin_reply_text .= "年齢：40歳〜49歳\n";
		} elseif( $clean['age'] === "5" ){
			$admin_reply_text .= "年齢：50歳〜59歳\n";
		} elseif( $clean['age'] === "6" ){
			$admin_reply_text .= "年齢：60歳〜\n";
		}
	
		$admin_reply_text .= "お問い合わせ内容：" . nl2br($clean['contact']) . "\n\n";
		
		// テキストメッセージをセット
		$body = "--__BOUNDARY__\n";
		$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
		$body .= $admin_reply_text . "\n";
		$body .= "--__BOUNDARY__\n";
	
		// ファイルを添付
		if( !empty($clean['attachment_file']) ) {		
			$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
			$body .= "Content-Transfer-Encoding: base64\n";
			$body .= "\n";
			$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
			$body .= "--__BOUNDARY__\n";
		}
	
		// 管理者へメール送信
		mb_send_mail( 'webmaster@gray-code.com', $admin_reply_subject, $body, $header);
		
	} else {
		$page_flag = 0;
	}	
}

function validation($data) {

	$error = array();

	// 氏名のバリデーション
	if( empty($data['your_name']) ) {
		$error[] = "「氏名」は必ず入力してください。";

	} elseif( 20 < mb_strlen($data['your_name']) ) {
		$error[] = "「氏名」は20文字以内で入力してください。";
	}
	// ふりがなのバリデーション
	if( empty($data['furigana']) ) {
		$error[] = "「ふりがな」は必ず入力してください。";
	
	} elseif( 20 < mb_strlen($data['furigana']) ) {
		$error[] = "「ふりがな」は20文字以内で入力してください。";
	} elseif( !preg_match('/\A[ぁ-ゟー]+\z/u',$data['furigana'])){
		$error[] = "「ふりがな」はすべてひらがなで入力してください。";
	}
	
	// 携帯電話番号のバリデーション
	if( empty($data['tel']) ) {
		$error[] = "「携帯電話番号」は必ず入力してください。";
	
	} elseif( !preg_match( '/^0[0-9]{9,10}$/', $data['tel']) ) {
		$error[] = "「携帯電話番号」は正しい形式で入力してください。";
	}

	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";

	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	}

	// 性別のバリデーション
	if( empty($data['gender']) ) {
		$error[] = "「性別」は必ず入力してください。";

	} elseif( $data['gender'] !== 'male' && $data['gender'] !== 'female' && $data['gender'] !== 'other') {
		$error[] = "「性別」は必ず入力してください。";
	}

	// 年齢のバリデーション
	if( empty($data['age']) ) {
		$error[] = "「年齢」は必ず入力してください。";

	} elseif( (int)$data['age'] < 1 || 6 < (int)$data['age'] ) {
		$error[] = "「年齢」は必ず入力してください。";
	}

	// お問い合わせ内容のバリデーション
	if( empty($data['contact']) ) {
		$error[] = "「お問い合わせ内容」は必ず入力してください。";
	}

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error[] = "プライバシーポリシーをご確認ください。";

	} elseif( (int)$data['agreement'] !== 1 ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}

	return $error;
}
?>

<!DOCTYPE>
<html lang="ja">
<head>
<title>お問い合わせフォーム</title>
<style rel="stylesheet" type="text/css">
body {
    margin: 0;
    max-width: 650px;
    margin: auto;
}

h1 {
	margin-bottom: 20px;
	padding: 20px 0;
	color: #07689f;
	font-size: 122%;
	border-top: 1px solid #999;
	border-bottom: 1px solid #999;
    text-align: center;
}

.contactForm{
    width: 100%;
    max-width: 650px;
    margin: 0 auto 20px;
    border-spacing: 0;    
}

.contactForm td{
    padding: 2rem 0;
    border-bottom: 1px solid #e1e1e1;
}

.text-center{
    text-align: center;
    padding: 10px;
}

input[type=text] {    
    background-color:transparent;
    border: 1px solid #d1d1d1;
    border-radius: 5px;
    box-shadow: none;
    box-sizing: boder-box;
    min-height: 1rem;
    padding: 10px;
    width: 100%;
    font-size: 16px;
}

input[type="text"]::placeholder{
    color: #aaa;
}

input[type="text"]:focus,
textarea[name=contact]:focus{
    border-color: #41487e;
    outline: 0;
}

input[type="text"]::placeholder{
    transition: 300ms;
}

input[type="text"]:focus::placeholder{
    transform: translatex(30px);
    opacity: 0;
}

input[name=btn_confirm],
input[name=btn_submit],
input[name=btn_back] {
	margin-top: 10px;
	padding: 5px 20px;
	font-size: 100%;
	color: #fff;
	cursor: pointer;
	border: none;
	border-radius: 3px;
	box-shadow: 0 3px 0 #2887d1;
	background: #4eaaf1;
}

input[name=btn_back] {
	margin-right: 20px;
	box-shadow: 0 3px 0 #777;
	background: #999;
}

label {
	display: inline-block;
	margin-bottom: 10px;
	font-weight: bold;
	width: 200px;
	vertical-align: top;
	text-align: left;
}

label:not(label[for=gender_male],
label[for=gender_female],
label[for=gender_other],
label[for=picture])::before{
	content: "必須";
	background-color: #4169e1;
	color: #fff;
	font-size: 12px;
	font-weight: bold;
	min-width: 10px;
	padding: 3px 7px;
	margin: 0px 5px;
	line-height: 1;
	vertical-align: middle;
	white-space: nowrap;
	text-align: center;
	border-radius: 10px;
	display: inline-block;
}

label[for=picture]::before{
	content: "任意";
	background-color: #4169e1;
	color: #fff;
	font-size: 12px;
	font-weight: bold;
	min-width: 10px;
	padding: 3px 7px;
	margin: 0px 5px;
	line-height: 1;
	vertical-align: middle;
	white-space: nowrap;
	text-align: center;
	border-radius: 10px;
	display: inline-block;
}

label[for=gender_male],
label[for=gender_female],
label[for=gender_other],
label[for=agreement] {
	margin-right: 10px;
	width: auto;
	font-weight: normal;
}

textarea[name=contact] {
    background-color:transparent;
    border: 1px solid #d1d1d1;
    border-radius: 5px;
    box-shadow: none;
    box-sizing: boder-box;
    min-height: 6rem;
    padding: 10px;
    width: 100%;
    font-size: 16px;
}

span{
	display:block;
	color:#87CEEB;
}

.error_list {
	padding: 10px 30px;
	color: #ff2e5a;
	font-size: 86%;
	text-align: left;
	border: 1px solid #ff2e5a;
	border-radius: 5px;
}
</style>
</head>
<body>
<h1>お問い合わせフォーム</h1>
<?php if( $page_flag === 1 ): ?>

<form method="post" action="">
	<table class="contactform">
		<tr>
			<td><label>氏名</label></td>
			<td><?php echo $clean['your_name']; ?></td>
		</tr>
		<tr>
			<td><label>ふりがな</label></td>
			<td><?php echo $clean['furigana']; ?></td>
		</tr>
		<tr>
			<td><label>携帯電話番号</label></td>
			<td><?php echo $clean['tel']; ?></td>
		</tr>
		<tr>
			<td><label>メールアドレス</label></td>
			<td><?php echo $clean['email']; ?></td>
		</tr>
		<tr>
			<td><label>性別</label></td>
			<td><?php if( $clean['gender'] === "male" ){ echo '男性'; }elseif($clean['gender'] === "female"){ echo '女性'; } else{echo'その他';}?></td>
		</tr>
		<tr>
			<td><label>年齢</label></td>
			<td><?php if( $clean['age'] === "1" ){ echo '〜19歳'; }
			elseif( $clean['age'] === "2" ){ echo '20歳〜29歳'; }
			elseif( $clean['age'] === "3" ){ echo '30歳〜39歳'; }
			elseif( $clean['age'] === "4" ){ echo '40歳〜49歳'; }
			elseif( $clean['age'] === "5" ){ echo '50歳〜59歳'; }
			elseif( $clean['age'] === "6" ){ echo '60歳〜'; } ?></td>
		</tr>
		<tr>
			<td><label>お問い合わせ内容</label></td>
			<td><?php echo nl2br($clean['contact']); ?></td>
        </tr>
		<?php if( !empty($clean['attachment_file']) ): ?>
			<tr>
				<td><label>画像ファイルの添付</label></td>
				<td><img src="<?php echo FILE_DIR.$clean['attachment_file']; ?>"></td>
			</tr>
	</table>
	<?php endif; ?>
	<div class="text-center">
		<p><?php if( $clean['agreement'] === "1" ){ echo 'プライバシーポリシーに同意する'; }else{ echo '同意しない'; } ?></p>
	</div>
	<div class="text-center">
		<input type="submit" name="btn_back" value="戻る">
		<input type="submit" name="btn_submit" value="送信">
	</div>
	<input type="hidden" name="your_name" value="<?php echo $clean['your_name']; ?>">
	<input type="hidden" name="furigana" value="<?php echo $clean['furigana']; ?>">
	<input type="hidden" name="email" value="<?php echo $clean['email']; ?>">
	<input type="hidden" name="gender" value="<?php echo $clean['gender']; ?>">
	<input type="hidden" name="age" value="<?php echo $clean['age']; ?>">
	<input type="hidden" name="contact" value="<?php echo $clean['contact']; ?>">
	<?php if( !empty($clean['attachment_file']) ): ?>
		<input type="hidden" name="attachment_file" value="<?php echo $clean['attachment_file']; ?>">
	<?php endif; ?>
	<input type="hidden" name="agreement" value="<?php echo $clean['agreement']; ?>">
</form>

<?php elseif( $page_flag === 2 ): ?>

<p>送信が完了しました。</p>

<?php else: ?>

<?php if( !empty($error) ): ?>
	<ul class="error_list">
	<?php foreach( $error as $value ): ?>
		<li><?php echo $value; ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
	<table class="contactform">
        <tr>
            <td><label>氏名</label></td>
            <td><input type="text" name="your_name" value="<?php if( !empty($clean['your_name']) ){ echo $clean['your_name']; } ?>"placeholder="例）山田太郎"></td>
        </tr>
        <tr>
            <td><label>ふりがな</label></td>
            <td><input type="text" name="furigana" value="<?php if( !empty($clean['furigana']) ){ echo $clean['furigana']; } ?>"placeholder="例）やまだたろう"></td>
        </tr>	
		<tr>
            <td><label>携帯電話番号</label></td>
            <td><input type="text" name="tel" value="<?php if( !empty($clean['tel']) ){ echo $clean['tel']; } ?>"placeholder="例）09012345678"></td>
        </tr>
        <tr>
            <td><label>メールアドレス</label></td>
            <td><input type="text" name="email" value="<?php if( !empty($clean['email']) ){ echo $clean['email']; } ?>"placeholder="例）guest@example.com"></td>
        </tr>
        <tr>
            <td><label>性別</label></td>
			<td>
				<label for="gender_male"><input id="gender_male" type="radio" name="gender" value="male" <?php if( !empty($clean['gender']) && $clean['gender'] === "male" ){ echo 'checked'; } ?>>男性</label>
				<label for="gender_female"><input id="gender_female" type="radio" name="gender" value="female" <?php if( !empty($clean['gender']) && $clean['gender'] === "female" ){ echo 'checked'; } ?>>女性</label>
				<label for="gender_other"><input id="gender_other" type="radio" name="gender" value="other" <?php if( !empty($clean['gender']) && $clean['gender'] === "other" ){ echo 'checked'; } ?>>その他</label>
			</td>
        </tr>
		<tr>
            <td><label>年齢</label></td>
			<td>
				<div>
					<select name="age">
						<option value="">選択してください</option>
						<option value="1" <?php if( !empty($clean['age']) && $clean['age'] === "1" ){ echo 'selected'; } ?>>〜19歳</option>
						<option value="2" <?php if( !empty($clean['age']) && $clean['age'] === "2" ){ echo 'selected'; } ?>>20歳〜29歳</option>
						<option value="3" <?php if( !empty($clean['age']) && $clean['age'] === "3" ){ echo 'selected'; } ?>>30歳〜39歳</option>
						<option value="4" <?php if( !empty($clean['age']) && $clean['age'] === "4" ){ echo 'selected'; } ?>>40歳〜49歳</option>
						<option value="5" <?php if( !empty($clean['age']) && $clean['age'] === "5" ){ echo 'selected'; } ?>>50歳〜59歳</option>
						<option value="6" <?php if( !empty($clean['age']) && $clean['age'] === "6" ){ echo 'selected'; } ?>>60歳〜</option>
					</select>
				</div>
			</td>
        </tr>
        <tr>
            <td><label>お問い合わせ内容</label></td>
            <td><textarea name="contact"><?php if( !empty($clean['contact']) ){ echo $clean['contact']; } ?></textarea></td>
        </tr>
        <tr>
            <td><label for="picture">画像ファイルの添付</label></td>
            <td><input type="file" name="attachment_file"></td>
        </tr>
	</table>
	<div class="text-center">
		<label for="agreement"><input id="agreement" type="checkbox" name="agreement" value="1" <?php if( !empty($clean['agreement']) && $clean['agreement'] === "1" ){ echo 'checked'; } ?>>プライバシーポリシーに同意する</label>
	</div>
	<div class="text-center">
		<input type="submit" name="btn_confirm" value="入力内容を確認する">
	</div>

</form>

<?php endif; ?>
</body>
</htm>