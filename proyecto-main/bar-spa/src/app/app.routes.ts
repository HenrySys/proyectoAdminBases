import { Routes } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
import { MainComponent } from './components/home/main/main.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { UsersComponent } from './components/dashboard/users/users.component';
import { ReservationsComponent } from './components/dashboard/reservations/reservations.component'
import { TablesComponent } from './components/dashboard/tables/tables.component';
import { MenuComponent } from './components/home/menu/menu.component';
import { ProductsComponent } from './components/dashboard/products/products.component';
import { ProvidersComponent } from './components/dashboard/providers/providers.component';
import { LoginComponent } from './components/auth/login/login.component';
import { RegisterComponent } from './components/auth/register/register.component';
import { AddOrdersComponent } from './components/dashboard/tables/add-orders/add-orders.component';
import { AddTableComponent } from './components/dashboard/tables/add-table/add-table.component';
import { PayOrderComponent } from './components/dashboard/tables/pay-order/pay-order.component';
import { OrdersComponent } from './components/dashboard/tables/orders/orders.component';
import { ErrorComponent } from './components/error/error.component';
import { AuthGuard } from './auth.guard';
import { ClientsComponent } from './components/dashboard/clients/clients.component';
import { EmployeesComponent } from './components/dashboard/employees/employees.component';
import { InventarioComponent } from './components/dashboard/inventario/inventario.component';

export const routes: Routes = [
  {
    path: '', component: HomeComponent,
    children: [{
      path: '',
      component: MainComponent
    }, {
      path: 'menu',
      component: MenuComponent
    }]
  },
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [AuthGuard],

    children: [{
      path: 'users',
      component: UsersComponent,
      canActivate: [AuthGuard],

    }, {
      path: 'reservations',
      component: ReservationsComponent,
      canActivate: [AuthGuard],

    }, {
      path: 'tables',
      component: TablesComponent,
      canActivate: [AuthGuard],

      children: [{
        path: '',
        component: AddTableComponent,
        canActivate: [AuthGuard],

      }, {
        path: 'add-orders',
        component: AddOrdersComponent,
        canActivate: [AuthGuard],

      },
      {
        path: 'pay-order',
        component: PayOrderComponent,
        canActivate: [AuthGuard],

      }]
    },{
      path: 'products',
      component: ProductsComponent,
      canActivate: [AuthGuard],

    },{
      path: 'inventario',
      component: InventarioComponent,
      canActivate: [AuthGuard],

    }, {
      path: 'providers',
      component: ProvidersComponent,
      canActivate: [AuthGuard],

    },
    {
      path: 'orders',
      component: OrdersComponent,
      canActivate: [AuthGuard],

    },{
      path: 'clients',
      component: ClientsComponent,
      canActivate: [AuthGuard],


    },{
      path: 'employees',
      component: EmployeesComponent,
      canActivate: [AuthGuard],

    }]
  },
  {
    path: 'auth/login',
    component: LoginComponent }
  ,{
    path: '**',
    component: ErrorComponent
  }
];
