export class Reserva{
    constructor(public id:number,public idUser:number, public idCliente:number, public idMesa:number,
        public fecha:Date , public hora:string, public estado:string
    ){}
  }
