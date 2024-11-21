import { Component } from '@angular/core';
import { RouterOutlet,RouterLink } from '@angular/router';
import { UserService } from '../../services/user.service';
import { User } from '../../models/user';
import { server } from '../../services/global';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [FormsModule,CommonModule,RouterOutlet,RouterLink],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent {
  public identity:any
  public user:User
  private checkIdentity;
  public peticionDirectaImgUrl:string=server.url+'user/getimage/' ;
  constructor(
      private _userService:UserService
  ){
      this.checkIdentity= setInterval(()=>{
          this.identity = this._userService.getIdentityFromStorage();
      },500);
      this.user = new User(1,"","","","");
  }
  ngOnDestroy(){
      clearInterval(this.checkIdentity);
  }

  isActionDrowdownOpen: boolean = false;
  toggleDropdowns2() {
    this.isActionDrowdownOpen = this.isActionDrowdownOpen ? false : true;
  }

  logOut(){
      this._userService.logout();
  }

  ClearLocalStorage(){
    localStorage.clear();
  }

  ClearSessionStorage(){
    sessionStorage.clear();
  }

}
