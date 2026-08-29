enum Roles {
    Invalid = -1,
    User = 0, //Default role
    Autoapproved = 1, //Your uploads are automatically approved
    Administrator = 2, //Can manage all uploads
    Developer = 3 //Receive messages (bug reports, suggestions, support)
}

export default Roles
