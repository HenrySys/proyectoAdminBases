import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { User } from '../../../models/user';
import { UserService } from '../../../services/user.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
  providers: [UserService]
})
export class LoginComponent {
  public status: number;
  public user: User;

  constructor(
    private _userService: UserService,
    private _router: Router,
    private _routes: ActivatedRoute
  ) {
    this.status = -1;
    this.user =  new User(1, "", "", "","");
  }

  ClearLocalStorage(){
    localStorage.clear();
  }

  onSubmit(form: any) {
    //debugger;
    console.log("paso0");
    this._userService.login(this.user).subscribe({
      next: (response: any) => {
        console.log("paso1.1");
        if (response.status != 401) {
          console.log("paso1");
          sessionStorage.setItem("token", response);
          this._userService.getIdentityFromAPI().subscribe({
            next: (resp: any) => {
              console.log("paso2");
              sessionStorage.setItem("identity", JSON.stringify(resp));
              let identity = JSON.parse(sessionStorage.getItem("identity") ?? "{}");
              if (identity.userType == "Admin" || identity.userType == "Empleado" ) {
                localStorage.clear();
                this._router.navigate(['dashboard']);
              } else {
                this._router.navigate(['menu']);
              }
            },
            error: (error: Error) => {
              console.log(error);
            }
          });
        } else {
          this.status = 0;
        }
      },
      error: (error: any) => {
        this.status = 1;
      }
    })
  }
}
