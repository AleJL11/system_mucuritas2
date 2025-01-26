import sys
import pywhatkit as kit

usuario     = sys.argv[1]
clave       = sys.argv[2]
nombre      = sys.argv[3]
#telefono    = sys.argv[4]

# Enviar mensaje de WhatsApp
mensajeWs = f"""
Hola {nombre}, bienvenido al sistema integral de consulta y registro de pagos del estacionamiento mucuritas 2.

Estos son sus datos para iniciar sesión:

Usuario: {usuario}
Clave: {clave}

¡Gracias por unirse a nuestro sitio, esperamos que disfrute su experiencia!
"""

try:
    #Enviar el mensaje de WhatsApp inmediatamente
    #kit.sendwhatmsg_instantly(telefono, mensajeWs, 15, True, 50)
    print("Mensaje de WhatsApp enviado exitosamente")
except Exception as e:
    print("Error al enviar el mensaje de WhatsApp:", str(e))