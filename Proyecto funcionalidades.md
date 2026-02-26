Vamos a crear una app web que resuelva la necesidades de los siguientes usuarios:

Roles: Empresa, Propietario, Trabajador.

Quiero añadir un SuperSu que pueda gestionar usuarios y roles. (último paso)

------------------------------------------------------------------------------------------------
rol de Empresa:
La empresa tiene acceso y control de las tareas, que serán el motor de esta web y que tienen todo el protagonismo ya que van a ser las que se registren diariamente y sin ellas el resto de aplicación no funcionaría. Cuanto más ordenado sea el registro de las tareas mejor funcionará la aplicación.
En las tareas se registran el día que se realiza, las horas trabajadas, el tipo de trabajo, los trabajadores que realizan la tarea, la parcela en la que se realiza la tarea, una descripción de la tarea.
Hay otro tipo de tareas que se plantean sin fecha para ir añadiendolas como pendientes. Y al ver estas tareas poder fecharlas para tener una visión de tareas a futuro. Si la tarea registrada tiene un trabajador le aparecerá a dicho trabajador en su app, si no tiene trabajador asignado solo la verá la empresa.
Es importante que las tareas se puedan buscar o filtrar de manera que sean fáciles de ver para hacer investigaciones.

Además tiene la capacidad de gestionar los trabajadores, las parcelas, los tipos de trabajo, los vehículos, las herramientas, el sistema de riego, el control de fitosanitarios, control de campaña y las finanzas.

Los trabajadores se identifican con un dni, nombre, apellidos, teléfono, correo electrónico.Además tienen que tener fecha alta en la seguridad social y fecha de baja en la seguridad social. Pueden estar activos o no activos en la cuadrilla regular. Tienen una imagen de perfil y podemos guardar una imagen con los documentos de identidad y Seguridad social.

Las parcelas tienen un número de catastro, Nombre o apodo, Propietario, número de olivos, tipo de olivos, año de plantación, Tipo de plantación(Tradicional, intensivo, superintensivo), Riego o Secano, Corta par, impar o siempre.
Dentro de la sección de cada parcela individual puede haber documentos como escrituras, permisos de riego. Además podemos hacer un resumen de la productividad de la parcela, cantidad de riego del año anterior (en caso de que tenga riego), el precio total que ha costado el trabajo anual por olivo y algún reporte más.

En cuanto al Propietario tenemos que tener su dni identificativo, además de una imagen del dni por ambas caras, nombre y apellidos, teléfono y correo electrónico.

Los tipos de trabajo tienen un título corto, descripción de la tarea, precio por hora.

Los vehículos tienen un nombre, una matrícula, Fecha matriculación, última fecha itv, imagen de ficha técnica y documentación, poliza de Seguro, precio de seguro.

Las herramientas tienen nombre, fecha de compra, precio, pdf de instrucciones y una breve descripción.

El sistema de riego tiene como intención registrar cada año el número de metros cúbicos de agua que se riega cada parcela, haciendose en diferentes fases quedando cada una de ellas registrada en los siguientes campos: Parcela, Hidrante, fecha apertura, fecha cierre, metros cúbicos apertura, metros cúbicos cierre.

El control de fitosanitarios es muy importante ya que tienen que quedar registrados el inventario de compra de dichos productos y la aplicación en cada parcela teniendo en cuenta la fecha y la cantidad suministrada. Crearemos una sección en la que se cuando se registre una tarea con el trabajo de Sulfato o Herbicida se genere una entrada con la parcela, el producto suministrado y la cantidad.

Este proyecto se hace de manera que las finanzas se automatízan a la hora de crear las tareas, por lo que cada tarea generará un coste a gestionar.
Tenemos una cuenta central que puede ser la liquidez (dinero total) de la empresa, esta liquidez se separa en cuenta bancaria y efectivo.

Al crear la tarea cada trabajador acumula el precio de la tarea por las horas que haya trabajado y se genera una deuda. Esta deuda se queda registrada hasta que llega el último día del mes que se suma el total de todas las tareas realizadas. Esto genera un registro mensual con el precio a pagar a cada trabajador.
Una vez pagada la deuda el empresario puede registrar el pago y el trabajador queda con deuda cero.

El empresario puede añadir gastos (compras, reparaciones, inversiones, seguros, impuestos, gestorías) e ingresos (Pagos por labores a terceros, subvenciones, liquidación del aceite). Ambos habrá que registrar en que tipo se percibe si cuenta bancaria y efectivo.

Durante la época de la campaña (recogida de la aceituna) se registra diariamente la tarea de "Recoger aceituna".
Por lo que tendrá una sección específica para ello.
Esta tarea tiene la peculiaridad de que hay que poner el número de kilos recogidos al día y 4 o 5 días más adelante registrar el rendimiento (porcentaje de Aceite por kilo de aceituna), este dato lo introduce la empresa cuando lo tiene disponible. Al finalizar la campaña a estos registros se les aplica un precio de venta que es lo que recibimos como "liquidación de la cooperativa" y este es nuestro ingreso económico principal como empresa, que tambien registraremos en un campo llamado "beneficio campaña" para poder hacer los cálculos. Por lo que es importante tener reportes de productividad, teniendo en cuenta el precio que se percibe al vender el aceite y compararlo con el coste de producción (La suma de todas las tareas realizadas durante el año por parcela). Es importante tener en cuenta que la época de la campaña empieza en noviembre y acaba en febrero/marzo. Por lo que al acabar la campaña empezaría a contar de cero el coste de producción. Los años o campañas se marcarian como Campaña 25/26.


------------------------------------------------------------------------------------------------
rol de Propietario:
El propietario de las parcelas al acceder a la aplicación puede ver sus parcelas, las tareas que se le realizan a su parcela (sin que vea los trabajadores, las horas trabajadas, ni el precio).
Puede ver datos de contacto de la empresa.



------------------------------------------------------------------------------------------------
rol de Trabajador:
El trabajador puede ver la deuda que tiene (lo que va a percibir por sus tareas realizadas).
Puede ver un calendario con las tareas que ha realizado, y tiene un botón para marcar días trabajados al mes (al final de cada mes se registra y se pone a cero). Tambien puede ver una lista de tareas pendientes que le puede asignar la empresa.
Puede ver datos de contacto de la empresa.