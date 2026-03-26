<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="color-scheme" content="light" />
        <meta name="supported-color-schemes" content="light" />
        <title>Restablecer contraseña</title>
    </head>
    <body style="margin:0;padding:0;background:#F8F9FA;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#F8F9FA;">
            <tr>
                <td align="center" style="padding:28px 16px;">
                    <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:640px;width:100%;background:#ffffff;border:1px solid rgba(0,0,0,0.08);border-radius:20px;overflow:hidden;">
                        <tr>
                            <td style="background:#008D62;padding:22px 24px;">
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#ffffff;letter-spacing:0.2px;">
                                    Restablecer contraseña
                                </div>
                                <div style="margin-top:6px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(255,255,255,0.85);">
                                    Prefabricados Alesa
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:22px 24px;">
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:20px;color:#111827;">
                                    Recibimos una solicitud para restablecer la contraseña de tu cuenta.
                                </div>
                                <div style="height:14px;line-height:14px;">&nbsp;</div>
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:20px;color:#111827;">
                                    Haz clic en el botón para crear una nueva contraseña.
                                </div>
                                <div style="height:18px;line-height:18px;">&nbsp;</div>

                                <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="background:#E98332;border-radius:14px;">
                                            <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 18px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#ffffff;text-decoration:none;">
                                                Restablecer contraseña
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <div style="height:18px;line-height:18px;">&nbsp;</div>
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:rgba(0,0,0,0.55);">
                                    Este enlace expirará en {{ (int) ($expireMinutes ?? 60) }} minutos.
                                </div>

                                <div style="height:18px;line-height:18px;">&nbsp;</div>
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:rgba(0,0,0,0.55);">
                                    Si tú no solicitaste este cambio, puedes ignorar este correo.
                                </div>

                                <div style="height:18px;line-height:18px;">&nbsp;</div>
                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:11px;line-height:16px;color:rgba(0,0,0,0.55);">
                                    Si el botón no funciona, copia y pega este enlace en tu navegador:
                                </div>
                                <div style="margin-top:6px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:11px;line-height:16px;word-break:break-all;">
                                    <a href="{{ $resetUrl }}" style="color:#008D62;text-decoration:none;">{{ $resetUrl }}</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 24px;background:#ffffff;">
                                <div style="margin-top:4px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:rgba(0,0,0,0.55);">
                                    Prefabricados Alesa
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>

