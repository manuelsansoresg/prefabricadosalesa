<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="color-scheme" content="light" />
        <meta name="supported-color-schemes" content="light" />
        <title>Nuevo mensaje de contacto</title>
    </head>
    <body style="margin:0;padding:0;background:#F8F9FA;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#F8F9FA;">
            <tr>
                <td align="center" style="padding:28px 16px;">
                    <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:640px;width:100%;background:#ffffff;border:1px solid rgba(0,0,0,0.08);border-radius:20px;overflow:hidden;">
                        <tr>
                            <td style="background:#008D62;padding:22px 24px;">
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#ffffff;letter-spacing:0.2px;">
                                    Nuevo mensaje de contacto
                                </div>
                                <div style="margin-top:6px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(255,255,255,0.85);">
                                    Prefabricados Alesa · {{ $submittedAt }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:22px 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:0 0 14px 0;">
                                            <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;font-weight:800;letter-spacing:0.12em;color:#E98332;text-transform:uppercase;">
                                                Datos del cliente
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid rgba(0,0,0,0.08);border-radius:16px;">
                                                <tr>
                                                    <td style="padding:16px 16px;border-bottom:1px solid rgba(0,0,0,0.06);">
                                                        <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:11px;line-height:14px;color:rgba(0,0,0,0.55);">
                                                            Nombre
                                                        </div>
                                                        <div style="margin-top:4px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:15px;line-height:20px;font-weight:700;color:#111827;">
                                                            {{ $name }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:16px 16px;border-bottom:1px solid rgba(0,0,0,0.06);">
                                                        <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:11px;line-height:14px;color:rgba(0,0,0,0.55);">
                                                            Correo
                                                        </div>
                                                        <div style="margin-top:4px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:15px;line-height:20px;font-weight:700;">
                                                            <a href="mailto:{{ $email }}" style="color:#008D62;text-decoration:none;">{{ $email }}</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:16px 16px;">
                                                        <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:11px;line-height:14px;color:rgba(0,0,0,0.55);">
                                                            Teléfono
                                                        </div>
                                                        <div style="margin-top:4px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:15px;line-height:20px;font-weight:700;color:#111827;">
                                                            {{ filled($phone ?? '') ? $phone : 'No proporcionado' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <div style="height:18px;line-height:18px;">&nbsp;</div>

                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;font-weight:800;letter-spacing:0.12em;color:#E98332;text-transform:uppercase;">
                                    Mensaje
                                </div>
                                <div style="margin-top:10px;border:1px solid rgba(0,0,0,0.08);border-radius:16px;padding:16px;background:#F8F9FA;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:20px;color:#111827;white-space:pre-wrap;">
                                        {{ $messageBody }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
