<?php namespace App\Mapeos\Controles;


 class ControlesMaestrosMultiples
{

     /*
      * Mapeos de los Controles
      */

     /*
      * ================== COMPRAS ====================
      */

     const CMM_MOTIVO_DEVOLUCION = 'CMM_MotivoDevolucion';

      /*
       * ================== MANUFACTURA ====================
       */

      // -------------------- TIPO CONDICIONES --------------------------
      public static $CMM_MAN_TIPO_CONDICIONES = 'CMM_MAN_TIPO_CONDICIONES';
      public static $CMM_MAN_TIPO_PROYECTO = 'CMM_MAN_TIPO_PROYECTO';

     /*
      * ================== FINANZAS ====================
      */

     // -------------------- TIPOS RELACIONES SAT --------------------------
     public static $CMM_TIPO_RELACION_01 = '50570111-3697-474C-99D6-2CDA34C2A5A5';
     public static $CMM_TIPO_RELACION_03 = '7C92DA2E-1EB3-4955-95C5-37942463B087';

     // -------------------- TIPOS RELACIONES SAT --------------------------
     public static $CMM_USO_CFDI_G02 = '2223CE19-DEEB-4722-8688-D603B4395D24';

     // -------------------- ENTIDADES FINANCIERAS --------------------------
     public static $CMM_BANC_EntidadFinanciera = 'CMM_BANC_EntidadFinanciera';
     // -------------------- FORMA PAGO CXC ---------------------------------
     public static $CMM_CCXC_FormaPagoCXC = 'CMM_CCXC_FormaPagoCXC';
     // -------------------- PROVISION DESCRIPCIONES CXC ---------------------------------
     public static $CMM_ProvisionDescripcionesCXC = 'CMM_ProvisionDescripcionesCXC';
     // -------------------- PROVISION ALERTAS CXC ---------------------------------
     public static $CMM_ProvisionAlertasCXC = 'CMM_ProvisionAlertasCXC';
     // -------------------- FORMA PAGO CXP ---------------------------------
     public static $CMM_CCXP_FormaPagoCXP = 'CMM_CCXP_FormaPagoCXP';
     // -------------------- TIPO PAGO CXP ---------------------------------
     public static $CMM_TipoPagoCXP = 'CMM_TipoPagoCXP';
      // -------------------- Concepto Nota Debito ---------------------------------
     public static $CMM_CCXC_ConceptoNotaDebito = 'CMM_CCXC_ConceptoNotaDebito';
     // -------------------- TIPO DE RETENCION ---------------------------------
     public static $CMM_CCXC_TipoRetencion = 'CMM_CCXC_TipoRetencion';
     // -------------------- CONCEPTOS NOTAS DE CREDITO ---------------------------------
     public static $CMM_CCXC_ConceptoNotaCredito = 'CMM_CCXC_ConceptoNotaCredito';
     //---------------------Cantidad Saldo CXP------
     public static $CMM_CantidadSaldoCXP = 'CMM_CantidadSaldoCXP';
     //---------------------Caracteristicas de Bultos------
     public static $CMM_PRO_CaracteristicasBultos = 'CMM_CaracteristicasBulto';
     //---------------------Tipos de Bultos------
     public static $CMM_PRO_TiposBultos = 'CMM_TipoBulto';
     //---------------------Monto Saldo CXC------
     public static $CMM_FIN_MontoSaldoFacturaCXC = 'CMM_FIN_MontoSaldoFacturaCXC';
     //---------------------Monto Saldo CXP------
     public static $CMM_FIN_MontoSaldoFacturaCXP = 'CMM_FIN_MontoSaldoFacturaCXP';

// -------------------- TIPO REGISTRO PAGO ---------------------------------
    //Clientes
    public static $CMM_PAGO_CLIENTE = '42B7AE60-A156-4647-A85B-56581A74B2B8';
    public static $CMM_PAGO_NOTA_CREDITO = "AACC069A-10E4-0C97-AA53-911AE2CFCE2C";
    public static $CMM_PAGO_SALDO_A_FAVOR = "D9BCE353-FEEB-4D17-898D-201E6AC356C0";
    public static $CMM_PAGO_FACTURA = "33718A33-2CBF-4ED2-85DA-CC22BD0D4DE6";
    public static $CMM_PAGO_FACTURA_MISCELANEA = "2688FE37-D52A-45D6-B2A6-1A6D34577756";

    //Proveedores
    public static $CMM_PAGO_PROVEEDOR = '1ED4BE9A-C32E-4973-827E-48B0AD254755';
    public static $CMM_PAGO_NOTA_DEBITO = "FA3DA289-F4DF-40E6-B370-33A84ED95DA5";
    public static $CMM_PAGO_SALDO_A_FAVOR_PROVEEDOR = "B820B23A-DF7F-4AA5-9C60-58A36EE46C6D";
    public static $CMM_PAGO_CREAR_ANTICIPO_PROVEEDOR = "7585E327-057B-409C-9D41-F19C3EFBD60F";
    public static $CMM_PAGO_APLICAR_ANTICIPO_PROVEEDOR = "23693D81-BB7D-4097-9B55-15C6B37C8FAD";
    public static $CMM_PAGO_FACTURA_PROVEEDOR = "0CED0DF1-FFF8-4B0E-8F96-4A0EAB4D0D11";
    public static $CMM_PAGO_FACTURA_MISCELANEA_PROVEEDOR = "095E3E00-72BB-4587-B289-BF17A513021F";

    // ------------------- FORMA PAGO CLIENTE --------------------
    public static $CMM_FormaPagoCXC_NOTA_CREDITO = "87A56F34-18D0-44FF-8560-90A7D6ABCE1D";
    public static $CMM_FormaPagoCXC_SALDO_A_FAVOR = "F86EC67D-79BD-4E1A-A48C-08830D72DA6F";
    public static $CMM_FormaPagoCXC_DONATIVO = "BE9A747B-F237-4B57-9C82-B979A925348E";
    public static $CMM_FormaPagoCXC_POSTFECHADO = "29308A29-02C9-4091-9135-A1016E8D91CD";

    // ------------------- FORMA PAGO PROVEEDOR --------------------
    public static $CMM_FormaPagoCXP_NOTA_DEBITO = "E479498D-83F3-4618-AC2D-F1936FFB3E55";
    public static $CMM_FormaPagoCXP_SALDO_A_FAVOR = "98E7B18A-A744-418F-9383-C50606F0066A";
    public static $CMM_FormaPagoCXP_DONATIVO = "07A084C5-890F-4615-87DC-505B1958854B";
    public static $CMM_FormaPagoCXP_POSTFECHADO = "E4AD5DE9-7D20-4A66-8E53-123CF31A16DF";

    //--------------------- TIPO CLIENTE AUTOSERVICIO -----------------------------------------
    public static $CMM_VEN_TipoCliente_Autoservicio = '0470AA58-D26E-47CA-BC29-2C0B1BA57F92';

    //--------------------- TIPO CLIENTE AUTOSERVICIO -----------------------------------------
    public static $ADM_CMM_TipoTransferenciaId = 'D42F56BA-7A6F-4B84-B993-BA1158252741';

    //---------------------TIPO DE REGISTRO DE PAGO ---------------------------------------
    public static $TipoRegistroCXC_ID = '42B7AE60-A156-4647-A85B-56581A74B2B8';

    //---------------------FORMA DE PAGO ---------------------------------------
    public static $FormaPagoCXC_ID = 'F86EC67D-79BD-4E1A-A48C-08830D72DA6F';

    //--------------------------TIPO DE FACTURA----------------------------------
    public static $CXC_TipoFactura_ID = '33718A33-2CBF-4ED2-85DA-CC22BD0D4DE6';

    //--------------------------TIPO NOTA DE CREDITO----------------------------------
    public static $CCXC_NotaCredito_ID = 'AACC069A-10E4-0C97-AA53-911AE2CFCE2C';

    /*
     * ================== INVENTARIO ====================
     */


    // -------------------- TALLA ARTICULOS ---------------------------------
     public static $CMM_INV_Talla = 'CMM_INV_Talla';
    // -------------------- COLOR ARTICULOS ---------------------------------
     public static $CMM_INV_Color = 'CMM_INV_Color';
     // -------------------- ARTICULOS ESPECIFICACIONES --------------------------
     public static $CMM_INV_ArticulosEspecificaciones = 'CMM_INV_ArticulosEspecificaciones';
     // -------------------- CODIGO DE CICLO ---------------------------------
     public static $CMM_INV_CodigoCiclo = 'CMM_INV_CodigoCiclo';
     // -------------------- METODO DE CONTEO ---------------------------------
     public static $CMM_INV_MetodoConteo = 'CMM_INV_MetodoConteo';
     // -------------------- MOTIVO DE AJUSTE ---------------------------------
     public static $CMM_INV_MotivoAjuste = 'CMM_INV_MotivoAjuste';
     // -------------------- MOTIVO DEVOLUCION TRASPASO ---------------------------------
     public static $CMM_MotivoDevolucionTraspaso = 'CMM_MotivoDevolucionTraspaso';
     // -------------------- MOTIVO SURTIMIENTO GASTO ---------------------------------
     public static $CMM_INV_MotivoSurtimientoGasto = 'CMM_INV_MotivoSurtimientoGasto';
     // -------------------- MOTIVO RECIBO TRANSFERENCIA ---------------------------------
     public static $CMM_INV_MotivoReciboTransferencia = 'CMM_INV_MotivoReciboTransferencia';
     // -------------------- MOVIMIENTO EN INVENTARIO ---------------------------------
     public static $CMM_INV_MovimientoInventario = 'CMM_INV_MovimientoInventario';
     // -------------------- SUBCATEGORIA ARTICULOS ---------------------------------
     public static $CMM_INV_SubcategoriaArticulos = 'CMM_INV_SubcategoriaArticulos';
     // -------------------- MARCAS ARTICULOS ---------------------------------
     public static $CMM_INV_MarcaArticulo = 'CMM_INV_MarcaArticulo';
     // -------------------- POLITICA DE ORDEN ---------------------------------
     public static $CMM_INV_PoliticaOrden = 'CMM_INV_PoliticaOrden';
     // -------------------- MANEJO INVENTARIO ---------------------------------
     public static $CMM_INV_ManejoInventario = 'CMM_INV_ManejoInventario';
     // -------------------- INVENTARIO FISICO ---------------------------------
     public static $CMM_CDA_MovimientoEnInventario = '39B95B52-F380-40E0-B543-F132A769E819';
     // -------------------- AJUSTE FISICO ---------------------------------
     public static $AJUSTE_DE_INVENTARIO = 'A9032E9D-294F-4BBB-A9AF-C0DD82891CED';
     // -------------------- CMM_EstatusSolicitudTraspaso ---------------------------------
     public static $CMM_EstatusSolicitudTraspaso_Solicitado = '9920BC29-0857-4C2C-9A87-5E296681B1A2';
     public static $CMM_EstatusSolicitudTraspaso_Traspasado = '9367F403-C6E8-4952-8276-F71B8E92B641';
     public static $CMM_EstatusSolicitudTraspaso_TraspasoParcial = '58B06E0A-7D6F-482A-AB9D-287FA7872E7E';
     public static $CMM_EstatusSolicitudTraspaso_Recibido = 'B8749E06-0936-4171-85C1-90615BCCE41E';
     public static $CMM_EstatusSolicitudTraspaso_RecibidoParcial = 'F39BE884-DDCD-4A1C-B8CB-38322917583B';
     public static $CMM_EstatusSolicitudTraspaso_CerradoPorUsuario = 'AFD19273-9945-49BA-B857-CBF06852F5D1';
    //-------------------- STATUS DE SOLICITUD TRASPASO-------------------------------------
    public static $CMM_EstatusSolicitudTraspaso_Recibo_Parcial = 'F39BE884-DDCD-4A1C-B8CB-38322917583B';
    public static $CMM_EstatusSolicitudTraspaso_Traspaso_Parcial = '58B06E0A-7D6F-482A-AB9D-287FA7872E7E';


    public static $CMM_EstatusOTG_Recibido = '3A807C8A-B3E2-4643-AC5C-5E980504F328';
    public static $CMM_EstatusOTG_Recibido_Parcial = '554E844F-9889-4E6F-94A0-769F4AA93507';




    //-------------------- Cancelación de Devolución de Traspaso ------------------------------
    public static $Cancelación_Devolución_Traspaso = '67CC2D5E-26D8-42A9-BB17-42F6A6B639E5';
    //-------------------- Cancelación de Devolución de Traspaso Recibo  ------------------------------
    public static $Cancelación_Devolución_Traspaso_Recibo = 'B18BBD9C-E9EE-4830-A30F-7CA6E2FAD3E4';


    //-------------------- STATUS DE LOTE-------------------------------------
     public static $CMM_EstatusLote_Abierto = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D';
     public static $CMM_EstatusLote_Empacado = '5F608B87-8FD8-4A0A-8C41-BFFAEAAC211F';
     public static $CMM_EstatusLote_ReciboParcial = '8601CEC0-3271-4EC6-B857-AE1D352208D8';
     public static $CMM_EstatusLote_Recibido = '98344A16-D332-4282-BD71-ED4FCC468D2F';
     public static $CMM_EstatusLote_Cerrado = '35402538-09B8-403B-A798-7EB626525CF7';
     //-------------------- STATUS PALLET --------------------------------------------
     public static $CMM_INV_EstatusPallet_Abierto = '0B0D3E21-E967-47C0-9E7E-34DBB9C6B5C4';
     public static $CMM_INV_EstatusPallet_Cerrado = 'E4AF6E7F-5542-45D3-85CD-137C124109FB';
     //-------------------- ARTICULOS ESPECIFICACIONES --------------------------------
     public static $CMM_INV_Sin_Empaque = '92555574-180D-4CB6-B080-63CAA688002E';
     public static $CMM_INV_Con_Empaque = '9760D770-E87C-414B-9DBA-EFCD77BEE29B';

     // ---------------------TRASPASOS-------------------------------
     public static $TRASPASO = 'D7D22076-0400-4C59-B88E-1AA98C910B9F';
     public static $CMM_TIPO_TRASPASO = 'F9BA141B-26F9-4C9E-AEEB-948D868CD6B3';

    // --------------------- ESTATUS TRASPASOS-------------------------------
    public static $TRASPASO_ESTATUS_REEVALUDADO = '5439FCEA-6EC4-4D72-AAE8-E40FF5D3798B';

     // -------------------RECIBO DE TRASPASOS------------------------
     public static $RECIBO_TRASPASO = '1267D672-3920-48DA-9FE7-5C35E31C1BF6';

     // ------------------CANCELACION DE TRASPASOS---------------------------
     public static $CANCELA_TRASPASO = '934C2C6F-0E97-479E-95EB-9556DC979B62';

     //--------------CANCELACION RECIBO DE TRASPASOS-------------------------
     public  static $CANCELA_RECIBO_TRASPASO = 'CF6E3FCD-2859-474C-844B-77E0F0BB4241';


    public static $RECIBO_SURTIMIENTO_SOLICITUD_TRASPASO = 'AC79620A-6B6D-44A3-983D-D2FD89E3B776';

    public static $SALIDA_PRODUCCION_WIP = '689F221C-DB76-4824-8D5C-71955013B1A7';

    public static $ENTRADA_PRODUCCION_WIP = 'ECB88DFF-00A2-440C-9374-FC7158B68EDF';


     /*
     * ================== RECURSOS HUMANOS ====================
     */

     // -------------------- TIPO EMPLEADO --------------------------
     public static $CMM_RH_TipoEmpleado = 'CMM_RH_TipoEmpleado';
     // -------------------- ESTADO CIVIL --------------------------
     public static $CMM_RH_EstadoCivil = 'CMM_RH_EstadoCivil';
     // -------------------- HABILIDADES --------------------------
     public static $CMM_RH_CalificacionHabilidades = 'CMM_RH_CalificacionHabilidades';
     // -------------------- TIPO SANGUINEO --------------------------
     public static $CMM_RH_TipoSanguineo = 'CMM_RH_TipoSanguineo';
     // -------------------- SEXO --------------------------
     public static $CMM_RH_Sexo = 'CMM_RH_Sexo';
     // -------------------- TABULADORES PUESTOS --------------------------
     public static $CMM_TabuladoresPuestos = "CMM_TabuladoresPuestos";
     public static $ID_AYUDANTE_CMM_TabuladoresPuestos = "F80DEEB6-2101-48A5-83CD-7FCCD72FA2D4";
     public static $ID_VENDEDOR_CMM_TabuladoresPuestos = "F3751061-E719-451F-A950-A716E0924657";
     public static $ID_SUPERVISOR_CMM_TabuladoresPuestos = '9A02D8FD-9406-45AC-AA67-60E3A44616F1';
     // -------------------- EMPLEADOS PARENTESCO --------------------------
     public static $CMM_RPT_EmpleadoParentesco = 'CMM_RPT_EmpleadoParentesco';
     // -------------------- EMPLEADOS NIVEL ACADEMICO --------------------------
     public static $CMM_RPT_EmpleadoNivelAcademico = 'CMM_RPT_EmpleadoNivelAcademico';
     // -------------------- EMPLEADOS CAPACIDADES ESPECIALES --------------------------
     public static $CMM_RPT_EmpleadoCapacidadesEspeciales = 'CMM_RPT_EmpleadoCapacidadesEspeciales';
    // -------------------- INCIDENCIAS --------------------------

    public static $CMM_INCIDENCIA_CUMPLEAÑOS = "C57DD010-DB9A-436A-A50A-A96473B62602";
    public static $CMM_INCIDENCIA_FALTA_INJUSTIFICADA = "AB247A3A-A761-466D-B791-F08F6526D2F9";
    public static $CMM_INCIDENCIA_FALTA_JUSTIFICADA = '9A02D8FD-9406-45AC-AA67-60E3A44616F1';
    public static $CMM_INCIDENCIA_LABORO_FUERA_DE_EMPRESA = 'D66E4275-58B5-4791-8629-EBA30D4ED7731';
    public static $CMM_INCIDENCIA_PERMISO_CON_GOCE_SUELDO = '33998E29-C8FB-4EF8-8F9A-2BFA3136ED9A';
    public static $CMM_INCIDENCIA_PERMISO_SIN_GOCE_SUELDO = '21DBEA85-3BD7-48E2-884C-3C56AD7E6925';
    public static $CMM_INCIDENCIA_RETARDO = '1B366D89-6501-4CBC-B565-C5C222C5B651';
    public static $CMM_INCIDENCIA_SUSPENSION = '5308C675-A871-42DD-A719-B3A0E568D510';
    public static $CMM_INCIDENCIA_VACACIONES = 'C7C0642D-9432-4BB9-AB7A-9DAA677A07E4';
    public static $CMM_INCIDENCIA_INCAPACIDAD = 'AC21E788-6631-4390-88DF-15F8C5D9D77C';

    // -------------------- TIPOS BONOS --------------------------

    public static $CMM_BONO_CUBRIMIENTO_META = "DDE08628-E141-4F7E-8E49-08D786E3AEA3";
    public static $CMM_BONO_SOBRECUBRIMIENTO_META = "0935B653-6B76-430C-AD8D-5E3E600457E1";

    // -------------------- PUESTOS TABULADORES VENDEDORAS --------------------------

    public static $CMM_TABULADOR_PUESTO_VENDEDORA_BASE = "581830B7-9BF4-4DD7-8C75-0A92565D7EED";
    public static $CMM_TABULADOR_PUESTO_VENDEDORA_CAPITANA = "1E934903-339F-42C6-BC53-8FDD5440BB77";
    public static $CMM_TABULADOR_PUESTO_VENDEDORA_FINES = "7557DAC8-D092-416D-A0D4-1BFAC7195C1D";
    public static $CMM_TABULADOR_PUESTO_VENDEDORA_APOYO = "4759EBB8-7416-4B04-B285-D95DF413D3B9";

     /*
      * ================== TRANSPORTES ====================
      */

     // -------------------- MARCA UNIDAD DE TRANSPORTE --------------------------
     public static $CMM_TRANS_MarcaUnidadTransporte = 'CMM_TRANS_MarcaUnidadTransporte';
     // -------------------- ASEGURADORA DE VEHICULO --------------------------
     public static $CMM_TRANS_AseguradoraVehiculo = 'CMM_TRANS_AseguradoraVehiculo';
     // -------------------- TIPO DE COMBUSTIBLE --------------------------
     public static $CMM_TRANS_TipoCombustible = 'CMM_TRANS_TipoCombustible';
     // -------------------- TIPO UNIDAD DE TRANSPORTE --------------------------
     public static $CMM_TRANS_TipoUnidadTransporte = 'CMM_TRANS_TipoUnidadTransporte';

     /*
     * ================== VENTAS ====================
     */

     // -------------------- LIBRE A BORDO --------------------------
     public static $CMM_VEN_LibreABordo = 'CMM_VEN_LibreABordo';
    // -------------------- LIBRE A COMPRAS --------------------------
    public static $OC_CMM_LibreABordo = 'OC_CMM_LibreABordo';
    // -------------------- METODO DE EMBARQUE COMPRAS --------------------------
    public static $OC_CMM_MetodoEmbarque = 'OC_CMM_MetodoEmbarque';
     //--------------------- Rangos De Productividad -----------------------------
    public static $CMM_EP_RangosProductividad = 'CMM_EP_RangosProductividad';
    // -------------------- Tara Minimo --------------------------
    public static $OCED_CMM_TaraMinimo = 'OCED_CMM_TaraMinimo';
    // -------------------- Tara Maximo --------------------------
    public static $OCED_CMM_TaraMaximo = 'OCED_CMM_TaraMaximo';
     // -------------------- LOTES DIAS --------------------------
     public static $CMM_VEN_LoteDias = 'CMM_VEN_LoteDias';
     // -------------------- METODO DE EMBARQUE --------------------------
     public static $CMM_VEN_MetodoEmbarque = 'CMM_VEN_MetodoEmbarque';
     // -------------------- MOTIVO DEVOLUCION DE EMBARQUE --------------------------
     public static $CMM_MotivoDevolucionEmbarque = 'CMM_MotivoDevolucionEmbarque';
     // -------------------- MOTIVO DEVOLUCION DE ADM --------------------------
     public static $CMM_MotivoDevolucionADM = 'CMM_MotivoDevolucionADM';
     // -------------------- TERRITORIO DE VENTAS --------------------------
     public static $CMM_VEN_TerritorioVentas = 'CMM_VEN_TerritorioVentas';
     // -------------------- TIPO DE DESECHO --------------------------
     public static $CMM_VEN_TipoDesecho = 'CMM_VEN_TipoDesecho';
     // -------------------- TIPO DE PRECIO --------------------------
     public static $CMM_VEN_TipoPrecio = 'CMM_VEN_TipoPrecio';
     // -------------------- TIPO DE DEVOLUCION --------------------------
     public static $CMM_VEN_TipoDevolucion = 'CMM_VEN_TipoDevolucion';

    // -------------------- TIPO DE COTIZACION --------------------------
    public static $CMM_VEN_TipoCotizacion = 'CMM_VEN_TipoCotizacion';
    // -------------------- TIPO DE COMPLEJIDAD --------------------------
    public static $CMM_VEN_Complejidad = 'CMM_VEN_Complejidad';
    // -------------------- TIPO DE PRIORIDAD --------------------------
    public static $CMM_VEN_Prioridad = 'CMM_VEN_Prioridad';
    // -------------------- TIPO DE CONDICIONES --------------------------
    public static $CMM_VEN_TipoCondiciones = 'CMM_VEN_TipoCondiciones';
    // -------------------- TIPO DE PRPYECTO EN COTIZACION --------------------------
    public static $CMM_VEN_TipoProyectoCotizacion = 'CMM_VEN_TipoProyectoCotizacion';

    // -------------------- TIPO DE ANALISTA --------------------------
    public static $CMM_VEN_AnalistaCotizacion = 'CMM_VEN_AnalistaCotizacion';
    // -------------------- TIPO DE VENDEDOR --------------------------
    public static $CMM_VEN_VendedorCotizacion = 'CMM_VEN_VendedorCotizacion';

    public static  $CMM_VEN_TipoProyectoOV = 'CMM_VEN_TipoProyectoOV';

    //--------------------- CARACTERISTICAS CALIDAD-------------------------//
    public static $CMM_VEN_CaracteristicasCalidad = 'CMM_VEN_CaracteristicasCalidad';

    //--------------------- PLAN DE ENTREGA-------------------------//
    public static $CMM_VEN_PlanEntrega = 'CMM_VEN_PlanEntrega';


    // -------------------- TIPO PROVEEDOR AGENTE ADUANAL --------------------------
     public static $PRO_CMM_TipoProoveedorId = 'PRO_CMM_TipoProoveedorId';
     // -------------------- TIPO DE CLIENTE --------------------------
     public static $CMM_VEN_TipoCliente = 'CMM_VEN_TipoCliente';
     // -------------------- TIPO DE TIENDA --------------------------
     public static $CMM_VEN_TipoTienda = 'CMM_VEN_TipoTienda';
     // -------------------- TIPO DE ORDEN DE VENTA --------------------------
     public static $CMM_VEN_TipoOrdenVenta = 'CMM_VEN_TipoOrdenVenta';
     public static $CMM_VEN_FormaVenta = 'CMM_VEN_FormaVenta';
     // -------------------- AGRUPADOR ARTICULO DE LISTA DE PRECIOS --------------------------
     public static $CMM_VEN_AgrupadorArticulo = 'CMM_VEN_AgrupadorArticulo';
     // -------------------- AGRUPADOR COMERCIAL DE LISTA DE PRECIOS --------------------------
     public static $CMM_VEN_AgrupadorComercial = 'CMM_VEN_AgrupadorComercial';
     // -------------------- Tipo de Datos Addendas --------------------------
     public static $CMM_TipoDatoAddenda = 'CMM_TipoDatoAddenda';
     // -------------------- Tipo de Datos Addendas --------------------------
     public static $CMM_TipoRuta = 'CMM_TipoRuta';
    // -------------------- TIPOS CANAL --------------------------
    public static $CMM_TipoCanalDistribucion = 'CMM_TipoCanalDistribucion';

    // -------------------- AGRUPADOR COMERCIAL DE LISTA DE PRECIOS --------------------------
     public static $CMM_VEN_TipoGiro = 'CMM_VEN_TipoGiro';

     // -------------------- ID TIPO PROMOCION --------------------------
     public static $ID_LINEAL_CMM_TipoPromocion = 'EED6096B-F08C-4290-B6ED-091149576245';
     public static $ID_COMBO_CMM_TipoPromocion = '66DC0C7F-15C0-4DD3-A30B-D80FAE0466DA';
     public static $ID_RANGO_CMM_TipoPromocion = '3FF623E8-4220-4E7B-95DE-A6B8C89A8AEA';

     // ------------------- PROMOCION TIPO COMBO CON PRECIO Y SIN PRECIO-------------------------------------
        public static $CMM_PreciosComboPromocion = 'CMM_PreciosComboPromocion';
        public static $ID_CP_CMM_PreciosComboPromocion = '6EFF6F9E-3DD5-46A0-8013-383FE32D1F15';
        public static $ID_SP_CMM_PreciosComboPromocion = '1F187180-2184-4594-9E25-93971E804F44';
     public static $CMM_EstructuraComercialTipoComponente = "CMM_EstructuraComercialTipoComponente";
     public static $ID_DEPARTAMENTOS_CMM_EstructuraComercialTipoComponente = "F4E72498-8DA7-4C88-B8A6-AC8213EEA5A9";
     public static $ID_CEDIS_CMM_EstructuraComercialTipoComponente = "7DB1E6AE-E64A-4E2E-93E9-814A1D0C204F";
     public static $ID_RUTAS_CMM_EstructuraComercialTipoComponente = "E14295C6-743D-44CE-965A-40CF593E7D71";
     public static $ID_CANALES_DISTRIBUCION_CMM_EstructuraComercialTipoComponente = "7D1CEE64-3585-4EAA-A42D-E0E19EDE0EEA";
     public static $ID_CLIENTES_CMM_EstructuraComercialTipoComponente = "B8201293-E679-400C-AEFE-FB1A774E4C3C";
     public static $ID_CLIENTES_DIRECCIONES_EMBARQUES_CMM_EstructuraComercialTipoComponente = "23ACC62F-9CC6-47CC-A16A-F92EAB91AEB5";
    public static $ID_FORMA_VENTA_PUNTOS_VENTA_CMM_EstructuraComercialTipoComponente = "B49E4009-8670-47C7-BCA7-AE0A06195FF1";
    public static $ID_FORMA_VENTA_CLIENTES_DIRECTOS_CMM_EstructuraComercialTipoComponente = "A1A7B27D-8F77-4964-8AEF-2999A7FED933";
    public static $ID_FORMA_VENTA_RUTAS_CMM_EstructuraComercialTipoComponente = "C60E4A00-13B1-4563-AAE6-B2855D655F74";
    public static $ID_PUNTOS_VENTA_CMM_EstructuraComercialTipoComponente = "43C0E227-3830-46B1-A0AD-74E7DB62633E";
    public static $ID_CAJAS_CMM_EstructuraComercialTipoComponente = "AC517920-ECD3-4F95-AF1A-AE735E3C00F8";

     // ------------------- PROMOCIÓN TIPO AGRUPADOR LISTA DE PRECIO ----------------------------------------
        public static $CMM_VEN_AgrupadorComercial_Lista_de_Precio = 'C763FD67-E081-4634-869B-B1C15A337962';

     public static $ID_AGREGAR_TIPO_ASIGNACION_RUTAS_CLIENTES = "66140B42-CE7E-43FE-A1E2-9996E82629F3";
     public static $ID_CAMBIAR_TIPO_ASIGNACION_RUTAS_CLIENTES = "505F9EA4-D62B-4DC3-B7E9-D5B0B2B72299";
     public static $ID_ELIMINAR_TIPO_ASIGNACION_RUTAS_CLIENTES = "2CD85796-7125-4FFB-A376-AFCE297A97FA";

     // -------------------- TIPOS METAS --------------------------
     public static $AUTOSERVICIO_TIPO_META = 'AD832983-D99A-4608-B153-1B569C8F7C1C';

     public static $DETALLE_TIPO_META = '7F1B0346-DD3E-42E2-B571-503437C7F931';

    // -------------------- METAS CRITERIOS ARTICULOS ------------------
    public static $META_CRITERIO_ARTICULO_MARCA= '356F8FCF-BFE7-48CE-9EA0-812A98A3FADB';
    public static $META_CRITERIO_ARTICULO_FAMILIA = 'AB67D820-7D9E-4BD9-88DE-EBA3E5DAE75D';
    public static $META_CRITERIO_ARTICULO_CATEGORIA = '1D374748-CAE4-4FE2-9462-5139C39C50C5';
    public static $META_CRITERIO_ARTICULO_SUBCATEGORIA = '63D12639-6C64-4648-BB38-3BD0AA863DC2';
    public static $META_CRITERIO_ARTICULO_ARTICULO = '9860AE6D-DA7C-4F9D-84D6-711E4DD54593';
    public static $META_CRITERIO_ARTICULO_MARCA_ARTICULO= '96E77152-95E3-4F2E-B68C-3169CE1659CD';
    public static $META_CRITERIO_ARTICULO_FAMILIA_ARTICULO = 'E9D0B742-75E1-4AB9-94AB-C80A5FB5BFD9';
    public static $META_CRITERIO_ARTICULO_CATEGORIA_ARTICULO = '924F8717-6906-4E15-9A16-5806CD32BB0B';
    public static $META_CRITERIO_ARTICULO_SUBCATEGORIA_ARTICULO = 'CFDDBD1B-6A9A-4031-AC7F-41135C798B2B';

    // -------------------- METAS CRITERIOS ESTRUCTURA ------------------
    public static $META_CRITERIO_RUTA= '58DF4827-0161-413F-94FB-685DA7688460';
    public static $META_CRITERIO_SUCURSAL = '006A1193-6FC5-4954-8E02-466ADEEBF72C';
    public static $META_CRITERIO_DISTRIBUIDOR = '5BFCD97C-C0B6-4DA2-B182-CDB517A109A2';

    // -------------------- METAS CRITERIOS ESTRUCTURA ------------------
    public static $META_CRITERIO_TIEMPO_MES= '86517D65-622B-4898-8F62-62CCAADD0578';
    public static $META_CRITERIO_TIEMPO_TRIMESTRE = '160470E5-F19F-4340-912D-CF2847F14FBC';

    // -------------------- METAS TIPOS ------------------
    public static $META_TIPO_UM= 'DB0E05B7-E1EC-4796-BC78-DBE15160B825';
    public static $META_TIPO_MONTO = '06A76E38-29A9-4624-9559-31C0B158E4EC';


    // -------------------- TIPO DE LISTA DE PRECIO --------------------------
     public static $CMM_VEN_TipoListaPrecio = 'CMM_VEN_TipoListaPrecio';


     // -------------------- MOTIVO CAMBIO EMPLEADO DE RUTA --------------------------
     public static $CMM_MotivoCambioRutaEmpleado = 'CMM_MotivoCambioRutaEmpleado';


     //--------------------- MovimientoEnInventario ----------------------------------
     public static $DevolucionEmbarqueId = '68A54A6E-1B81-4B0B-B4A2-DAE8D0618DA0';

    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_OC = '034717C3-1A76-485F-AA10-0F2E83829B32';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_RECIBO_OC = '819A7E9B-E9FA-4503-ABB5-1C6848BC14B6';
    public static $ID_MOVIMIENTO_INVENTARIO_REEVALUACION_DEVOLUCION_RECIBO_OC = 'C244EBF6-D08A-4760-BA98-7FA8A1534BFF';
    public static $ID_MOVIMIENTO_INVENTARIO_REEVALUACION_RECIBO_OC = 'F15CF9B5-21F3-4D6F-BFF3-3E274B0877F4';
    public static $ID_MOVIMIENTO_INVENTARIO_REEVALUACION_LIBRE = 'AD790CE9-9BA3-4444-AF56-771DE98933C8';
    public static $ID_MOVIMIENTO_INVENTARIO_REEVALUACION_CONGELAMIENTO = '0AB89E09-1E8D-42B5-8947-8A02BC4C0F73';
    public static $ID_MOVIMIENTO_INVENTARIO__DEVOLUCION_RECIBO_OC = '819A7E9B-E9FA-4503-ABB5-1C6848BC14B6';
    public static $ID_MOVIMIENTO_INVENTARIO_SURTIMIENTO_OT = '0E70E1AC-F566-4393-ADEE-6715D73A8BF0';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_SURTIMIENTO_OT = '6747AB62-7A35-4D41-BCF5-D2035028D62E';
    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_SURTIMIENTO_OT = '21B19AC6-3DA1-4723-8FDC-41223C9AA4D6';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_RECIBO_SURTIMIENTO_OT = '5B9A6E9C-0CEF-456A-8213-61138D183940';
    public static $ID_MOVIMIENTO_INVENTARIO_ASIGNACION_MATERIAL = '50D25D91-547C-4DB0-8314-F1CC36518647';
    public static $ID_MOVIMIENTO_INVENTARIO_ASIGNACION_MATERIAL_POR_MERMA = 'CB1B1C98-8711-4504-8BB1-B6816328B471';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_ASIGNACION_MATERIAL = '8C5E596C-B2A9-4172-947C-5FE5F7C46990';
    public static $ID_MOVIMIENTO_INVENTARIO_ENTREGA_OT_WIP = '4AF9B573-8933-4325-B20A-7851797F4EC5';
    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_OT_WIP = 'CA975215-DD43-4060-9DF4-306D8325E99A';
    public static $ID_MOVIMIENTO_INVENTARIO_ENTREGA_WIP = '47BAE709-47AE-4A36-94A7-60C2BE02299D';
    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_WIP = 'AE7A488F-3C40-457E-877C-7B3305559B7E';
    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_OT = 'A98EEB00-F422-46CA-A45D-6B6AF2DEEF2A';
    public static $ID_MOVIMIENTO_INVENTARIO_RECIBO_OT_IMPLOSIONADO = '4B25E664-9098-43EF-AFF1-3F9BFFA814C6';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_RECIBO_OT = 'EC078623-5E78-4047-9672-543D0DB5AB3D';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_ENTREGA_OT = '9E3ECC26-E706-4A77-ACB0-009B63783CB2';
    public static $ID_MOVIMIENTO_INVENTARIO_SURTIMIENTO_OTG = '25B0EE46-48D1-4E47-B97C-33C3C67D6801';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_CANTIDAD_TRABAJADA_AJUSTE = '6645F4F4-76F2-4340-AC4E-0F636A26FE89';
    public static $ID_MOVIMIENTO_INVENTARIO_CANTIDAD_TRABAJADA_OT = '741B6B0A-363E-4085-8B45-5B3A4FB3E59D';
    public static $ID_MOVIMIENTO_INVENTARIO_DEVOLUCION_CANTIDAD_TRABAJADA_OT = '1B9249E8-3281-473E-A2EB-AD9D496A858A';

    public static $ID_MOVIMIENTO_INVENTARIO_ENTREGA_RECIBO_LOTE = '23479CD0-B6EA-409C-BA67-6F0780E6DC9C';

     //--------------------- STATUS OV------------------------------------------------
     public static $CMM_VEN_EstadoOV_Abierta = '3CE37D96-1E8A-49A7-96A1-2E837FA3DCF5';
     public static $CMM_VEN_EstadoOV_Embarque_Parcial = '3C387542-8DFC-42CC-8C49-5B6D32092C0C';
     public static $CMM_VEN_EstadoOV_Embarque_Completo = 'D528E9EC-83CF-49BE-AEED-C3751A3B0F27';

     //--------------------- Cancelación de Devolución de Embarque ---------------------------
     public static $Cancelación_Devolución_Embarque = '33F13AE6-AD89-4181-B899-F094A44CACFF';


    public static $ID_SUPERVISOR_EMPAQUE_TABULADOR_PUESTOS = '91472668-0AA4-48A6-B568-5D4A9221DD8C';

    //--------------------- TIPO RUTA COMISIONES  ---------------------------
    public static $RUTA_COMISION_TRADICIONAL = 'F455B5A0-51E8-45FF-BFE8-3EA39BE9EC60';
    public static $RUTA_COMISION_MULIIX = '6228FB99-6C26-4F13-AF48-77E95AF7E2BA';
    public static $RUTA_COMISION_MAYORISTA = 'D88E12A9-71CE-4413-B799-09D588BE0E7B';
    public static $RUTA_COMISION_PREVENTA = 'CA91FFAB-2C19-4519-BCD1-2855D0412098';
    public static $RUTA_COMISION_FORANEA = 'D0F8B907-5D25-4B48-8DFC-68B46537F2BB';

    //--------------------- METODOS PRONOSTICOS  ---------------------------
    public static $SIN_TENDENCIA_SIN_ESTACIONALIDAD = 'FCDD4EEA-E706-432D-BA1C-7B46492F8197';
    public static $ESTACIONALIDAD_ADITIVA_SIN_TENDENCIA = 'E2449EE1-844D-4448-942A-94DE5A2ED6F9';
    public static $ESTACIONALIDAD_MULTIPLICATIVA_SIN_TENDENCIA = '9571DACB-C49A-42FF-8F92-3D3AF69E09F8';
    public static $TENDENCIA_ADITIVA_SIN_ESTACIONALIDAD = '43DDF13E-8D63-43CC-9822-F3771A463078';
    public static $TENDENCIA_ADITIVA_ESTACIONALIDAD_ADITIVA = 'F9BF7982-624D-4150-B140-B6989A9747FE';
    public static $TENDENCIA_ADITIVA_ESTACIONALIDAD_MULTIPLICATIVA = '2E61FAB2-60A4-4536-903C-40B364F1D143';
    public static $TENDENCIA_MULTIPLICATIVA_SIN_ESTACIONALIDAD = '2DD779F4-6C90-4BB0-973A-B097F6AAFAC8';
    public static $TENDENCIA_MULTIPLICATIVA_ESTACIONALIDAD_ADITIVA = '74EA5CB9-F922-4FB5-A0E3-BCA379B5B03D';
    public static $TENDENCIA_MULTIPLICATIVA_ESTACIONALIDAD_MULTIPLICATIVA = '10D2AD97-1C3E-4491-B1F5-C94E92E7D12F';

    //--------------------- CRITERIO TIEMPO PRONOSTICOS  ---------------------------
    public static $MESES_PRONOSTICOS = 'E17C4EDE-9158-4CD3-AFBB-F3E7B02BC46D';
    public static $SEMANAS_PRONOSTICOS = '99997A0C-7D2E-481D-829B-115B4D0A99CC';
    public static $DIAS_PRONOSTICOS = '0A5F41EF-AF17-4ADA-A5B0-53BE676D6202';

    //--------------------- ESTATUS ORDEN TRABAJO  ---------------------------
    public static $OT_STATUS_ABIERTA = '3C843D99-87A6-442C-8B89-1E49322B265A';
    public static $OT_STATUS_EN_PRODUCCION = 'A488B27B-15CD-47D8-A8F3-E9FB8AC70B9B';
    public static $OT_STATUS_CERRADA_COSTEADA = '3E35C727-DAEE-47FE-AA07-C50EFD93B25F';
    public static $OT_STATUS_RECIBIDA = 'F860806C-B1EC-4047-AA95-EDAD406DE10E';
    public static $OT_STATUS_CERRADA_POR_USUARIO = '3887AF19-EA11-4464-A514-8FA6030E5E93';
    public static $OT_STATUS_CERRADA_COSTEADA_MATERIAL = '46B96B9F-3A45-4CF9-9775-175C845B6198';
    public static $OT_STATUS_CERRADA_COSTEADA_MATERIAL_PARCIAL = '7246798D-137A-4E94-9404-1D80B777EE09';
    public static $OT_STATUS_FINALIZADA = 'BAE3CB83-5827-42B5-9CBD-B397D6F29000';


     /*
     * ================== GENERAL ====================
     */

     // -------------------- TIPO DEPARTAMENTO --------------------------
     public static $CMM_TipoDepartamento = 'CMM_TipoDepartamento';
     // -------------------- ID TIPO DEPTO SUCURSAL --------------------------
     public static $ID_SUCURSAL_CMM_TipoDepartamento = '5845CCF9-23B9-41C7-B49F-A8495A7C4D08';

    // -------------------- ID TIPO DEPTO GERENCIA PRODUCCION --------------------------
    public static $ID_GERENCIA_PRODUCCION_CMM_TipoDepartamento = 'FC9240AB-BA89-4E1C-B31D-9D44D178B2BE';
    // -------------------- ID TIPO DEPTO DEPARTAMENTO PRODUCCION --------------------------
    public static $ID_DEPARTAMENTO_PRODUCCION_CMM_TipoDepartamento = '2D0DA641-8E75-4E7A-BC08-6AC624D41C7B';

    public static $CMM_FORMA_EMPAQUE = "CMM_FormaEmpaque";

    public static $CMM_MOTIVO_SEGUIMIENTO_OT = "CMM_MotivoSeguimientoOT";

     /*
     * ================== TRANSPORTES ====================
     */

     // -------------------- NIVEL COMBUSTIBLE --------------------------
     public static $CMM_NivelCombustible = 'CMM_NivelCombustible';
     // -------------------- DESTINO VEHICULO --------------------------
     public static $CMM_DestinoVehiculo = 'CMM_DestinoVehiculo';

    /*
     * ================== ESTADO OC ====================
     */

    public static $ID_ESTADO_OC_ABIERTA = '0589CDF3-3175-4501-A47E-BAAEC11B3D60';
    public static $ID_ESTADO_OC_CERRADA = 'DF4DA5D4-B56C-4319-89D5-A3010359BADA';
    public static $ID_ESTADO_OC_RECIBIDA_PARCIAL = '4C9DCE78-3461-4499-A579-8DDD5179B941';
    public static $ID_ESTADO_OC_RECIBIDA_COMPLETA = '0825D10C-15F4-4C8F-A3E9-4C480E00068D';
    public static $ID_ESTADO_OC_ETIQUETADA = '5A8C87F2-B6F0-4580-A581-F1A7DBF70C54';

    /*
     * ================== TIPOS PARTIDAS OC ====================
     */

    public static $ID_TIPO_PARTIDA_OC_ARTICULOS= '780769BC-1DF2-4F79-ADC4-A9AEC21370F5';
    public static $ID_TIPO_PARTIDA_OC_MISCELANEOS = '2AF823A9-0979-4844-A35B-8F2AE756B412';

}

 class CMM_VEN_EstadoOV {

     public static $OV_Abierta = "3CE37D96-1E8A-49A7-96A1-2E837FA3DCF5";
     public static $OV_CerradaPorUsuario = "2209C8BF-8259-4D8C-A0E9-389F52B33B46";
     public static $OV_EmbarqueParcial = "3C387542-8DFC-42CC-8C49-5B6D32092C0C";
     public static $OV_EmbarqueCompleto = "D528E9EC-83CF-49BE-AEED-C3751A3B0F27";
     public static $OV_FacturadoParcial = "C580C240-44D7-4CE7-9EED-339F2DA967F5";
     public static $OV_FacturadoCompleto = "90CAC435-DE6B-4148-BD20-16BCE3112936";

     // -------------------- ESTADO OV --------------------------
     public static $CMM_VEN_EstadoOV = 'CMM_VEN_EstadoOV';
 }


 class CMM_CCXC_TIPO_PAGO {

     public static $NOTA_CREDITO = "AACC069A-10E4-0C97-AA53-911AE2CFCE2C";
     public static $SALDO_A_FAVOR = "42B7AE60-A156-4647-A85B-56581A74B2B8";
     public static $FACTURA = "33718A33-2CBF-4ED2-85DA-CC22BD0D4DE6";

     // -------------------- ESTADO OV --------------------------
     public static $CMM_CCXC_TIPO_PAGO = 'CMM_CCXC_TIPO_PAGO';
 }
