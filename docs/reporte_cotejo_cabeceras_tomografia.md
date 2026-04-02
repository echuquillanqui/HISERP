# Reporte de cotejo de cabeceras (Tomografía)

Este documento compara las cabeceras solicitadas para el reporte operativo de tomografía contra los datos que actualmente registra el sistema en los módulos de **Órdenes de Tomografía**, **Resultados** y **Control de Insumos**.

## Resultado del cotejo

| Cabecera solicitada | ¿Existe hoy? | Fuente actual en sistema | Observación |
|---|---|---|---|
| N° | Parcial | `id` de orden / correlativo visual | Se puede usar `id` o numeración del reporte; no hay campo dedicado de ítem en planilla. |
| FECHA | Sí | `created_at` (orden) y `result_date` (resultado) | Definir si se reporta fecha de orden o fecha de informe. |
| Nombre y apellido del paciente | Sí | Relación `order -> patient` | Disponible en listado y edición. |
| DNI | Sí | `patient.dni` | Disponible en órdenes/resultados. |
| ORDEN DE SERVICIO | Sí | `order_tomographies.code` | Corresponde al código tipo `OT-000001`. |
| Tipo de tomografía | Sí | `order_tomographies.items -> radiography.description` | Se maneja como uno o varios estudios por orden. |
| S/C C/C | No (directo) | — | No existe un campo explícito “sin/con contraste”. |
| USO IOPAMIDOL | Sí | `tomography_results.iopamidol_used` | Se registra en ml en el resultado. |
| CONVENIO | Sí | `order_tomographies.agreement_id` | Disponible cuando aplica convenio. |
| SERVICIO | Parcial | `service_type` | Existe tipo de servicio (Emergencia/Particular/Convenio), validar equivalencia con cabecera. |
| MEDIO PAGO | Sí | `payment_type` | Incluye efectivo, yape, transferencia y pendiente. |
| EFECTIVO | Parcial | Derivado de `payment_type = CASH` | No está como columna independiente persistida. |
| YAPE | Parcial | Derivado de `payment_type = YAPE` | No está como columna independiente persistida. |
| TRANSF. BANCARIA | Parcial | Derivado de `payment_type = TRANSFER` | No está como columna independiente persistida. |
| POR COBRAR | Parcial | Derivado de `payment_type = PENDING_PAYMENT` | No está como columna independiente persistida. |
| PLACAS ENTREGADAS | Sí | `tomography_results.plates_used` | Campo actual de placas usadas en informe. |
| SALDO PLACAS | Sí | `tomography_supply_controls.plates_balance` | Se calcula en control de insumos. |
| SALDO IOPAMIDOL | Sí | `tomography_supply_controls.iopamidol_balance` | Se calcula en control de insumos. |
| MEDICO SOLICITANTE | Sí | `tomography_results.requesting_doctor_id` | Seleccionado en formulario de resultado. |
| Doctor (informe) | Sí | `tomography_results.report_signer_id` | Responsable que firma informe. |
| MEDIO | Sí | `order_tomographies.care_medium` | Ambulatorio o ambulancia. |
| BOLETA O FACTURA | Sí | `order_tomographies.document_type` | Boleta / Factura / sin documento. |
| N° BV Y FAC | Sí | `order_tomographies.document_number` | N° de documento comercial. |
| RECEPCION | No | — | No existe campo explícito de recepción en el flujo actual. |

## Brechas encontradas

1. Falta campo explícito para **S/C C/C**.
2. Falta campo explícito para **RECEPCION**.
3. En **MEDIO PAGO** las subcolumnas (Efectivo/Yape/Transferencia/Por cobrar) se pueden derivar del tipo de pago, pero no se guardan como columnas independientes.
4. Conviene definir regla de negocio de **FECHA** (orden vs informe) para evitar doble interpretación.

## Recomendación mínima para cerrar cabeceras al 100%

- Agregar a la orden o al resultado:
  - `contrast_type` (`SIN_CONTRASTE`, `CON_CONTRASTE`) para **S/C C/C**.
  - `reception_user` o `reception_note` para **RECEPCION**.
- En el export/report builder, mapear `payment_type` a columnas binarias de la plantilla:
  - EFECTIVO, YAPE, TRANSF. BANCARIA, POR COBRAR.
- Fijar un criterio único de **FECHA** (sugerido: fecha de orden para producción y fecha de informe para auditoría clínica).
