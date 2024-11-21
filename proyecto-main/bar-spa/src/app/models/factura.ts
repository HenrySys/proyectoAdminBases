export class Factura {
  constructor(public id:number, public idPedido:string, public idCliente:string, public idEmpleado:number,  public descuento:number,public total:number){}
}
