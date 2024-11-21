import { Component } from '@angular/core';
import { MesaService } from '../../../../services/mesa.service';
import { Mesa } from '../../../../models/mesa';
import Swal from 'sweetalert2';
import { timer } from 'rxjs';
import { OnInit, } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { initFlowbite } from 'flowbite';
import { RouterLink } from '@angular/router';
import { PedidoService } from '../../../../services/pedido.service';
import { Pedido } from '../../../../models/pedido';
import { Cliente } from '../../../../models/cliente';
import { Empleado } from '../../../../models/empleado';
import { NavigationEnd, Router } from '@angular/router';
import { ClienteService } from '../../../../services/cliente.service';
import { EmpleadoService } from '../../../../services/empleado.service';

@Component({
  selector: 'app-add-table',
  standalone: true,
  imports: [FormsModule, CommonModule],
  templateUrl: './add-table.component.html',
  styleUrl: './add-table.component.css',

})
export class AddTableComponent implements OnInit {

  public mesa: Mesa;
  public pedido: Pedido;

  public cliente : Cliente;
  public clienteLists: Cliente [];
  public empleadoLists: Empleado [];
  public empleado: Empleado;
  public MesaLists: Mesa[];
  private status: number;
  public filteredCliente: Cliente[];
  public filteredEmpleado: Empleado[];

  constructor(

    private MesaService: MesaService,
    private PedidoService: PedidoService,
    private ClienteService: ClienteService,
    private EmpleadoService: EmpleadoService,
    private router: Router,

  ) {

    this.mesa = new Mesa(1, 1, 1, "");
    this.pedido = new Pedido(0, 1, 1, 1, 1, new Date(), "", "");
    this.MesaLists = [];
    this.status = -1;
    this.cliente = new Cliente(1,1,"","","","","");
    this.clienteLists = [];
    this.filteredCliente = this.clienteLists;
    this.empleadoLists = [];
    this.empleado = new Empleado(1,1,"","","","");
    this.filteredEmpleado = this.empleadoLists;




  }


  ngOnInit(): void {

    initFlowbite();
    this.getClientes();
    this.getEmpleados();

    this.getTables();
  }

  selectedClienteId: number | null = null;


  selectClient(id: number): void {
    this.selectedClienteId = id// Asigna el ID al input
    this.pedido.idCliente = id; // Asigna el ID al pedido
    this.isDropdownOpenClients.fill(false); // Cierra todos los dropdowns
  }

  getClientes() {
    this.ClienteService.getClientes().subscribe({
      next: (response: any) => {
        console.log(response);
        this.clienteLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredCliente = this.clienteLists;
      },
      error: (error: any) => {
        console.error("Error al obtener los Clientes", error);
      }
    });
  };

  selectedEmpleadoId: number | null = null;


  selectEmpleado(id: number): void {
    this.selectedEmpleadoId = id
    this.pedido.idEmpleado = id;
    this.isDropdownOpenEmployee.fill(false); // Cierra todos los dropdowns
  }

  getEmpleados() {
    this.EmpleadoService.getEmpleados().subscribe({
      next: (response: any) => {
        console.log(response);
        this.empleadoLists = response.data;
        // Asegúrate de que estás accediendo a 'data' en la respuesta
        this.filteredEmpleado = this.empleadoLists;
      },
      error: (error: any) => {
        console.error("Error al obtener los Empleados ", error);
      }
    });
  };


  isDropdownOpenClients: boolean[] = [];

  toggleDropdownCliets(index: number) {
    this.isDropdownOpenClients[index] = !this.isDropdownOpenClients[index];
  }

  isDropdownOpenEmployee: boolean[] = [];

  toggleDropdownEmployee(index: number) {
    this.isDropdownOpenEmployee[index] = !this.isDropdownOpenEmployee[index];
  }


  isDropdownOpen:  boolean = false;
  selectedMesaId: number | null = null;


  toggleDropdown(mesaId: number) {
    this.isDropdownOpen = true ;
    this.selectedMesaId = mesaId;
    this.pedido.idMesa = mesaId;
  }

  CloseToggleDropdown() {
    this.isDropdownOpen = false;
    this.selectedMesaId = null;
  }


  changeStatus(st: number) {
    this.status = st;
    let countdown = timer(3000);
    countdown.subscribe(() => {
      this.status = -1;
    });
  }



  getTables() {
    this.MesaService.getMesas().subscribe({
      next: (response: any) => {
        console.log(response);
        this.MesaLists = response.data;
      },
      error: (error: any) => {
        console.error("Error al obtener las mesas", error);
      }
    });
  }

  onSubmitTable(form: any) {

    console.log(this.mesa);
    this.MesaService.store(this.mesa).subscribe({
      next: (response: any) => {
        console.log(response);
        form.reset();
        this.getTables();

        this.changeStatus(1);
        Swal.fire({
          icon: 'success',
          title: 'Mesa guardada exitosamente',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          location.reload();
        });
      },
      error: (error: any) => {
        console.error("Error al guardar la mesa", error);
        this.changeStatus(0);
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar la mesa',
          showConfirmButton: false,
          timer: 1500
        });
      }
    });

  }


  addPedido(idMesa: number, time: string) {
    console.log('Mesa seleccionada:', idMesa); // Verifica qué valor se envía
    console.log('Hora seleccionada:', time); // Verifica la hora seleccionada
    const now = new Date();
    const fecha = now.toISOString().split('T')[0]; // YYYY-MM-DD format

    this.pedido.idMesa = idMesa;
    this.pedido.idEmpleado = this.selectedEmpleadoId ?? 0;
    this.pedido.idCliente = this.selectedClienteId ?? 0;
    this.pedido.fecha = new Date(fecha);
    this.pedido.hora = time;
    this.pedido.estado = "En proceso";

    console.log(this.pedido);
    this.PedidoService.store(this.pedido).subscribe({
        next: (response: any) => {
            console.log('Respuesta del backend:', response);

            // Guardar el pedido completo devuelto por el backend en el Local Storage
            const pedidoCompleto = response.pedido; // Objeto completo del pedido
            localStorage.setItem('pedido', JSON.stringify(pedidoCompleto)); // Convierte a string y guarda

            console.log('Pedido guardado en Local Storage:', pedidoCompleto);

            this.pedido = pedidoCompleto;
            this.changeStatus(1);
            this.selectedMesaId = null;
            this.selectedEmpleadoId = null;
            this.selectedClienteId = null;
            this.isDropdownOpen = false;

            Swal.fire({
                icon: 'success',
                title: 'Generado pedido exitosamente',
                showConfirmButton: false,
                timer: 999
            }).then(() => {
                this.router.navigate(['/dashboard/tables/add-orders']);
            });
        },
        error: (error: any) => {
            console.error("Error al guardar el pedido", error);
            this.changeStatus(0);
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar el pedido',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}


  saveToLocalStorage(pedido: any) {
    localStorage.setItem('pedido', JSON.stringify(pedido));
  }

  times = [
    { id: '11-am', label: '11:00 AM', value: '11:00 AM' },
    { id: '11-30-am', label: '11:30 AM', value: '11:30 AM' },
    { id: '12-pm', label: '12:00 PM', value: '12:00 PM' },
    { id: '12-30-pm', label: '12:30 PM', value: '12:30 PM' },
    { id: '1-pm', label: '1:00 PM', value: '1:00 PM' },
    { id: '1-30-pm', label: '1:30 PM', value: '1:30 PM' },
    { id: '2-pm', label: '2:00 PM', value: '2:00 PM' },
    { id: '2-30-pm', label: '2:30 PM', value: '2:30 PM' },
    { id: '3-pm', label: '3:00 PM', value: '3:00 PM' },
    { id: '3-30-pm', label: '3:30 PM', value: '3:30 PM' },
    { id: '4-pm', label: '4:00 PM', value: '4:00 PM' },
    { id: '4-30-pm', label: '4:30 PM', value: '4:30 PM' },
    { id: '5-pm', label: '5:00 PM', value: '5:00 PM' },
    { id: '5-30-pm', label: '5:30 PM', value: '5:30 PM' },
    { id: '6-pm', label: '6:00 PM', value: '6:00 PM' },
    { id: '6-30-pm', label: '6:30 PM', value: '6:30 PM' },
    { id: '7-pm', label: '7:00 PM', value: '7:00 PM' },
    { id: '7-30-pm', label: '7:30 PM', value: '7:30 PM' },
    { id: '8-pm', label: '8:00 PM', value: '8:00 PM' },
    { id: '8-30-pm', label: '8:30 PM', value: '8:30 PM' }
  ];




}
