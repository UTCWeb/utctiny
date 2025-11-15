<?php
use Platformsh\ConfigReader\Config;
$platformsh = new Config();

if (getenv('PLATFORM_RELATIONSHIPS')) {
    // set base URL
    $wlabarron_saml_yourls_base_url = getenv('APP_URL') ?: '';
    // Add a slash at end of base URL
    $wlabarron_saml_yourls_base_url = rtrim($wlabarron_saml_yourls_base_url, '/') . '/';
    $wlabarron_saml_settings = array(
        // If 'strict' is True, then the PHP Toolkit will reject unsigned
        // or unencrypted messages if it expects them signed or encrypted
        // Also will reject the messages if not strictly follow the SAML
        // standard: Destination, NameId, Conditions ... are validated too.
        'strict' => true,

        // Enable debug mode (to print errors)
        'debug' => false,

        // Set a BaseURL to be used instead of try to guess
        // the BaseURL of the view that process the SAML Message.
        // Ex. http://sp.example.com/
        //     http://example.com/sp/
        'baseurl' => $wlabarron_saml_yourls_base_url . 'admin/auth',

        // Service Provider Data that we are deploying
        'sp' => array(
            // Identifier of the SP entity  (must be a URI)
            'entityId' => $wlabarron_saml_yourls_base_url . 'admin/auth/metadata.php',
            // Specifies info about where and how the <AuthnResponse> message MUST be
            // returned to the requester, in this case our SP.
            'assertionConsumerService' => array(
                // URL Location where the <Response> from the IdP will be returned
                'url' => $wlabarron_saml_yourls_base_url . 'admin/auth/acs.php',
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-POST binding only
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            // If you need to specify requested attributes, set a
            // attributeConsumingService. nameFormat, attributeValue and
            // friendlyName can be omitted. Otherwise remove this section.
            // "attributeConsumingService"=> array(
            //        "serviceName" => "SP test",
            //        "serviceDescription" => "Test Service",
            //        "requestedAttributes" => array(
            //            array(
            //                "name" => "",
            //                "isRequired" => false,
            //                "nameFormat" => "",
            //                "friendlyName" => "",
            //                "attributeValue" => ""
            //          )
            //      )
            //),
            // Specifies info about where and how the <Logout Response> message MUST be
            // returned to the requester, in this case our SP.
            'singleLogoutService' => array(
                // URL Location where the <Response> from the IdP will be returned
                'url' => $wlabarron_saml_yourls_base_url . 'admin/auth/slo.php',
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ),
            // Specifies constraints on the name identifier to be used to
            // represent the requested subject.
            // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

            // Usually x509cert and privateKey of the SP are provided by files placed at
            // the certs folder. But we can also provide them with the following parameters
            //'x509cert' => '',
            //'privateKey' => '',

            /*
             * Key rollover
             * If you plan to update the SP x509cert and privateKey
             * you can define here the new x509cert and it will be
             * published on the SP metadata so Identity Providers can
             * read them and get ready for rollover.
             */
            // 'x509certNew' => '',
        ),

        // Identity Provider Data that we want connect with our SP
        'idp' => array(
            // Identifier of the IdP entity  (must be a URI)
            'entityId' => getenv('idp_entityId') ?: 'https://cas.utc.edu/idp',
            // SSO endpoint info of the IdP. (Authentication Request protocol)
            'singleSignOnService' => array(
                // URL Target of the IdP where the SP will send the Authentication Request Message
                // Upsun env:var
                'url' => getenv('idp_singleSignOnService_url') ?: 'https://cas.utc.edu/cas/idp/profile/SAML2/Redirect/SSO',
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ),
            // SLO endpoint info of the IdP.
            'singleLogoutService' => array(
                // URL Location of the IdP where the SP will send the SLO Request
                'url' => '',
                // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
                // if not set, url for the SLO Request will be used
                'responseUrl' => '',
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ),
            // Public x509 certificate of the IdP
            'x509cert' => getenv('idp_x509cert') ?: 'MIIDDjCCAfagAwIBAgIVALvFq0A5kzk2+isTYA1uz5mcxMAfMA0GCSqGSIb3DQEB CwUAMBYxFDASBgNVBAMMC2Nhcy51dGMuZWR1MB4XDTIyMDUxMDIwNDYzNVoXDTQy MDUxMDIwNDYzNVowFjEUMBIGA1UEAwwLY2FzLnV0Yy5lZHUwggEiMA0GCSqGSIb3 DQEBAQUAA4IBDwAwggEKAoIBAQCErVOgCwC3qQKRybb3+I1N8kF1NzhjacPjQIjk DoXrmvBn7AX58+xHf0Y1GMkkini9wY2QwILDZsPp7Oni3yh+kTNX+WIsKt+oHoXn c/buq847b1wR8sLHfgv/CfQt66R2HiIGmg+yYa9VLLuwV9KlWPuC5Bgxkgnn+0Wd tK+vPdICBU0EeoTP0KaS/gmzmkJe4mRij6jZZCgyBAaFiFS8go4g7RMkRB7DuQsq +X1oijmk14S7DCxuDYhfu/VrS1XlFTSdIp69PoWeLYR4vc+R0RjnGFUWhie9CHlz tb2ka6JalEdWBnUsJx3zBvHH8Ld3nllLkOwZCvKM4/sfnDDRAgMBAAGjUzBRMB0G A1UdDgQWBBRsg1riGfnVkeiIf7CdcKJdNRD9vzAwBgNVHREEKTAnggtjYXMudXRj LmVkdYYYY2FzLnV0Yy5lZHUvaWRwL21ldGFkYXRhMA0GCSqGSIb3DQEBCwUAA4IB AQAwLKe/gDWwHaPpSLaQZwnNGfpMCyC8bcD60hkhxGb7RsejpGUWcLeTluOpi0r4 Cz18z2RTI1CikwG1H1GGxy6+OqKBU8MXSJr8wNyC5Bsrf9SJ6qYfFsSRgeV+Dgj2 9BlYRm6DcrDsjjPamaJemQqUMAy2ggxoTiUfVFeoWllC3QANsjZ8lKUTgUZS0g5a LiAWcXBW/Yu77c/Y3//l+O4sTBmaKphA+x0UEkyHFLglJO8DHDNqVHur+LoaKGs9 AxkTPHqmbyrdq8TMDgWd+s5vYynLJI5pB1+ksRFEG8xVnbCDHx8b9OIUNXD8x3w6 feD2xxdSAN++tGoYvZxmyRO3',

            /*
             *  Instead of use the whole x509cert you can use a fingerprint in
             *  order to validate the SAMLResponse, but we don't recommend to use
             *  that method on production since is exploitable by a collision
             *  attack.
             *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
             *   or add for example the -sha256 , -sha384 or -sha512 parameter)
             *
             *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
             *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
             *  'sha1' is the default value.
             */
            // 'certFingerprint' => '',
            // 'certFingerprintAlgorithm' => 'sha1',

            /* In some scenarios the IdP uses different certificates for
             * signing/encryption, or is under key rollover phase and more
             * than one certificate is published on IdP metadata.
             * In order to handle that the toolkit offers that parameter.
             * (when used, 'x509cert' and 'certFingerprint' values are
             * ignored).
             */
            // 'x509certMulti' => array(
            //      'signing' => array(
            //          0 => '<cert1-string>',
            //      ),
            //      'encryption' => array(
            //          0 => '<cert2-string>',
            //      )
            // ),
        ),
    );
}
