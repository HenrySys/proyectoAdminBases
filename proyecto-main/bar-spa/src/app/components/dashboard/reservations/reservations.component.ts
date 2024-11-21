import { Component, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Reserva } from '../../../models/reserva';
import { ReservaService } from '../../../services/reserva.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-reservations',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './reservations.component.html',
  styleUrl: './reservations.component.css'
})
export class ReservationsComponent implements OnInit{
  public reserva: Reserva;
  public reservaLists: Reserva[];

  constructor(
    private _ReservaService: ReservaService,

  ) {

    this.reserva = new Reserva(0, 0, 0, 0, new Date(), "", "");
    this.reservaLists = [];
  }

  ngOnInit(): void {
    this.getReservas();
  }

  store() {
    console.log("entre");
    this.reserva.fecha = new Date()//.toISOString().split('T')[0];
    this.reserva.hora = new Date().toTimeString().split(' ')[0];
    this.reserva.estado = 'pendiente';
    let user = JSON.parse(sessionStorage.getItem('identity')??'{}');
    console.log(user);
    this.reserva.idUser = user.iss;
    this.reserva.idMesa = 1;
    this._ReservaService.store(this.reserva).subscribe({
      next: (res) => {
        console.log(res);
      },
      error: (err) => {
        console.error('Error al guardar la reserva', err);
      }
    });
  }

  getReservas() {
    this._ReservaService.getReservas().subscribe({
      next: (res) => {
        console.log(res);
        this.reservaLists = res['data'];
        console.log(this.reservaLists);
      },
      error: (err) => {
        console.error('Error al obtener las reservas', err);
      }
    });
  }

  delete(id: number) {
    this._ReservaService.delete(id).subscribe({
      next: (res) => {
        console.log(res);
        location.reload();
      },
      error: (err) => {
        console.error('Error al eliminar la reserva', err);
      }
    });
  }

}
