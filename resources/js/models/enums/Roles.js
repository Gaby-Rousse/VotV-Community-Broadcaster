var Roles;
(function (Roles) {
    Roles[Roles["Invalid"] = -1] = "Invalid";
    Roles[Roles["User"] = 0] = "User";
    Roles[Roles["Autoapproved"] = 1] = "Autoapproved";
    Roles[Roles["Administrator"] = 2] = "Administrator";
    Roles[Roles["Developer"] = 3] = "Developer"; //Receive messages (bug reports, suggestions, support)
})(Roles || (Roles = {}));
export default Roles;
