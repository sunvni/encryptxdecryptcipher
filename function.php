<?php
const PG_PUBLIC_KEY = <<<PLK
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA48R1EMKzRvr4S8Kf2YEp
XluoTrJiVCh0I9vNMZXmVmJs0K65I8FYRvAXH0eD5sw3o6SaZdv/6KmdDxF75MrC
60qjJNEJVBk8N5uA3bDsyc0ZSkfPFn3pyNDNC/mXC5eC3kTrKupEX0M2LF0OtSnt
SFwckW9E/+2bEsBPnJJ04xOyUWoOAG3HqtcqQX8FitnrczZGYIb0wmf5cO3tWPZv
fZNmXJzXjAl8iizjxad6ooHnlTXIVuHE/QHu1QIP4RQX2j0csvCKQ0paW2ACP2uS
eXplOfqtf7xFrgjiH9Tj62+war2Tb7EVALZPukOLBKiNPPS8+xykTR+2mreSTOjE
cwIDAQAB
-----END PUBLIC KEY-----
PLK;

$cardinfo = array(
    "cardNo" => "xxxxxx",
    "expire" => "201907",
    "securityCode" => "199",
    "holderName" => "Hoang Hieu",
    "tokenNumber" => "4450939992645495"
);

function makeEncrypedCardInfo($map)
{
    $OPENSSL_CIPHER_NAME = "AES-128-CBC";
    $ivlen = openssl_cipher_iv_length($OPENSSL_CIPHER_NAME);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $cardInfoEncryptRaw = openssl_encrypt(
        json_encode($map),
        $OPENSSL_CIPHER_NAME,
        PG_PUBLIC_KEY,
        OPENSSL_RAW_DATA,
        $iv
    );
    $hmac = hash_hmac('sha256', $cardInfoEncryptRaw, PG_PUBLIC_KEY, $as_binary = true);
    $cardInfoEncrypted = base64_encode($iv.$hmac.$cardInfoEncryptRaw);
    return $cardInfoEncrypted;
}


function decryptCardInfo($data)
{
    $OPENSSL_CIPHER_NAME = "AES-128-CBC";
    $c = base64_decode($data);
    $ivlen = openssl_cipher_iv_length($OPENSSL_CIPHER_NAME);
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $decryptedData = openssl_decrypt(
        $ciphertext_raw,
        $OPENSSL_CIPHER_NAME,
        PG_PUBLIC_KEY,
        OPENSSL_RAW_DATA,
        $iv
    );
    $calcmac = hash_hmac('sha256', $ciphertext_raw, PG_PUBLIC_KEY, $as_binary = true);
    if (hash_equals($hmac, $calcmac)) {
        return $decryptedData;
    }
}

$encrypted = makeEncrypedCardInfo($cardinfo);
$decrypted = decryptCardInfo($encrypted);
echo "Encrypted:\n". $encrypted;
echo "\n";
echo "Decrypted:\n";
var_dump(json_decode($decrypted));

/*const PG_PRIVATE_KEY = <<<PRV
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA48R1EMKzRvr4S8Kf2YEpXluoTrJiVCh0I9vNMZXmVmJs0K65
I8FYRvAXH0eD5sw3o6SaZdv/6KmdDxF75MrC60qjJNEJVBk8N5uA3bDsyc0ZSkfP
Fn3pyNDNC/mXC5eC3kTrKupEX0M2LF0OtSntSFwckW9E/+2bEsBPnJJ04xOyUWoO
AG3HqtcqQX8FitnrczZGYIb0wmf5cO3tWPZvfZNmXJzXjAl8iizjxad6ooHnlTXI
VuHE/QHu1QIP4RQX2j0csvCKQ0paW2ACP2uSeXplOfqtf7xFrgjiH9Tj62+war2T
b7EVALZPukOLBKiNPPS8+xykTR+2mreSTOjEcwIDAQABAoIBAFq8XNZ8eecA/AFS
W1POvKg2Y2pWbg2QwBO48JmcWdJ4C1lnAaJY184Kv2talhVPraqnXErPxbbuOv9R
u8V9cQFDDpMQI2M5Wl/ctw2Z+fq6liSdTsZrCsNRSx5GJAIeLahWVEkYYnyAzggG
WmGZfkmhSFA3v4klIu3pZs29GouLAJoMTOUN12MYZpmEzRGghvc+dRJNzw0N6cQw
4Kg9dPLDIJiDbpA2hvpFfFpK+zXNtKXlEx330fKekxVVgA0LqHjKZrIA9H9+J1Wj
a2JgwVNVXLvjzfgBpDvuYpTNM3yUvvy1B7EJFtLFFacjgwiL1RFj4ZWQpuOx7Vnd
KG/FQ+ECgYEA/NKJRyrcDHu2doWloFV9pM0932NJmX+jpX6D2EcCc2gR5OInwe+k
Bsih1uUuUh/C+TgWzrYq7s1TaGbAo6hpSzQnAaQIX4BYfF4isf0ipUnuYUNaAYmF
nWpP5MZdQJDttzTsaWS8FNFkbQMQT2Y06j+YhjIe09zQ/tzjnF42BA8CgYEA5qFO
S0XLgBtmvEPqRkcmxZJsiN6KQcchc6gaQgHZ+BTMlRe8cA9EFxszDOEHZo0/jeHe
mjX2kp9hR+mGDBBjrPQH6ZiAAkfDPnoEFDtrccsc8GEmAdvwVJbY0CKFFpvPPpRD
QZhD4H6+pKoqx2eNbcD9HIgYy1YjfDR8+857BV0CgYBGcBXlKRDDvZf5b4TUpdzq
lHAfk9cGmCQs0JHFKQuKwbzyivvOsYh+h1lnuNRt9wFoU/Muxlwxyizp0m7radlk
JXSUpXHbwbNlewiplEAZ0v5CRPSHpxv93ofB0m5atcY1G96eAn3QQwJ6yLa7mFs3
xF4nTUF/f28PAtW7VBgyiwKBgQCg88f5Pj9UK0tabidMXpGPZq26I1znpPoShim1
ESe2O4W/My5+IhlJ6uCIx27rwf2tnglFJA3tq68viajUOIfnhvMSsiv762s16d94
4zML2k1a8OGegIjL7+5l6wFoktpieQQq/gG45ZtUvbFXMkyAYSQDynNLTNU89ECZ
VzOBvQKBgQD1WzpMBGWIVWFUwosCmTUupLM7T3bJRaGdV6zFLNEaHZSTjiD1EgEq
rS88u4GfFLmwYNPrkf3Dt/3JdTdtwvdxj3ZKSAVDoKELyuwWcCzHNilrJwa1pamq
xNQ/zNkxzMRA3kpP5sWybHd21D9KEQ/+Q869iWCor+F65hFwidWarg==
-----END RSA PRIVATE KEY-----
PRV;*/
