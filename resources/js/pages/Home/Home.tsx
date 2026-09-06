import {useState} from "react";
import {Link} from "react-router";
import {useAuth} from "../../contexts/AuthContext.tsx";
import {isAuthenticated} from "../../models/interfaces/User.ts";

export default function Home() {
    return (
        <div className="vcb">
            <Nav/>
            <div className="home">
                <Content/>
            </div>
        </div>
    );
}

export function Nav() {
    const [isOpen, setIsOpen] = useState(false);
    const toggleMenu = () => setIsOpen(!isOpen);
    const {user} = useAuth();

    return (
        <nav>
            <div className="flex flex-row h-16">
                <div className="ml-auto flex items-center sm:hidden">
                    <button
                        onClick={toggleMenu}
                        type="button"
                        className="mr-4 relative inline-flex items-center justify-center p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:ring-2 focus:ring-white focus:outline-hidden focus:ring-inset"
                        aria-controls="mobile-menu"
                        aria-expanded={isOpen}
                    >
                        {isOpen ? (
                            <svg className="block size-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5"
                                 stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        ) : (
                            <svg className="block size-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5"
                                 stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                        )}
                    </button>
                </div>

                <div className="hidden sm:flex flex-row gap-4 mr-4 ml-auto">
                    <Link className="mt-auto mb-auto p-2 w-28" to="/">Home</Link>
                    <Link className="mt-auto mb-auto p-2 w-28" to="/radio">Radio</Link>
                    <Link className="mt-auto mb-auto p-2 w-28" to="/tv">TV</Link>
                    <Link className="mt-auto mb-auto p-2 w-28" to="/upload">Upload</Link>
                    {isAuthenticated(user) ? (
                        <Link className="mt-auto mb-auto p-2 w-28" to="/account">Account</Link>
                    ) : (
                        <Link className="mt-auto mb-auto p-2 w-28" to="/login">Login</Link>
                    )}
                </div>
            </div>

            <div className="sm:hidden" id="mobile-menu">
                <div
                    className={`fixed top-15 right-0 bottom-0 w-1/2 bg-black/80 z-10 flex flex-col gap-2 transition-transform duration-300 ${
                        isOpen ? "translate-x-0" : "translate-x-full"
                    }`}
                >
                    <Link className="mx-auto p-2 w-36" to="/">Home</Link>
                    <Link className="mx-auto p-2 w-36" to="/radio">Radio</Link>
                    <Link className="mx-auto p-2 w-36" to="/tv">TV</Link>
                    <Link className="mx-auto p-2 w-36" to="/upload">Upload</Link>
                    {isAuthenticated(user) ? (
                        <Link className="mx-auto p-2 w-36" to="/account">Account</Link>
                    ) : (
                        <Link className="mx-auto p-2 w-36" to="/login">Login</Link>
                    )}
                </div>
            </div>
        </nav>
    );
}

export function Content() {
    return (
        <>
            <h1 className="text-center my-5 title text-4xl md:text-5xl lg:text-6xl">
                VotV Community Broadcaster
            </h1>

            <div className="panels">
                <div className="panel mx-auto xl:m-0">
                    <div className="subtitle">
                        Description
                    </div>
                    <div className="text flex flex-col gap-2 mt-2 mb-6">
                        <div>
                            VotV Community Broadcaster is a fan made website and is not affiliated with MrDrNose.
                        </div>
                        <div>
                            Its purpose is to provide an in-game radio station and tv station that broadcasts medias
                            shared by the community.
                        </div>
                        <br/>
                        <div>
                            Don't know how to add the streams to your online.txt? Check out the{" "}
                            <Link to="/help">Help</Link> page.
                        </div>
                    </div>

                    <div className="subtitle">
                        Features
                    </div>
                    <div className="text flex flex-col gap-2 mt-2 mb-2">
                        <div>Access a radio and tv station both available 24/7</div>
                        <div>Upload files that will be automatically parsed into these stations</div>
                        <div>Adjust file metadata</div>
                        <div>Download files uploaded by the community</div>
                    </div>
                </div>

                <div className="panel links">
                    <div className="subtitle text-center">
                        Links
                    </div>
                    <div className="flex relative flex-col gap-4 mt-2 mb-6 items-center sm:items-baseline">
                        <Link className="p-2 w-full" to="/changelog">Changelog</Link>
                        <Link className="p-2" to="/credits">Credits</Link>
                        <Link className="p-2" to="/history">History</Link>
                        <Link className="p-2" to="/help">Help</Link>

                        <div className="flex flex-row w-full justify-between gap-10">
                            <a className="p-2 w-40 home-button" href="https://votvbroadcast.com/txt/radio.txt">
                                Radios Streams
                            </a>
                            <a className="p-2 w-40 home-button" href="https://votvbroadcast.com/txt/tv.txt">
                                TV Streams
                            </a>
                        </div>
                        <div className="flex flex-row w-full justify-between gap-10">
                        <Link className="p-2 w-40 home-button" to="/suggestions">Suggestions</Link>
                        <Link className="p-2 w-40 home-button" to="/bugs">Bugs</Link>
                        </div>

                        <div className="text-lg sm:text-xl md:text-2xl lg:text-3xl subtitle">External</div>
                        <div className="flex flex-row w-full justify-between gap-10">
                            <a className="p-2 w-40 home-button" href="https://votv.dev">votv.dev</a>
                            <a className="p-2 w-40 home-button" href="https://assets.votvbroadcast.com/">Assets Hub</a>
                        </div>

                        <a
                            className="p-2 w-90 home-button"
                            href="https://github.com/Foxaryse/ArchiveCommunityBranch/tree/main/linux/scripts/onlinevideoworkaround"
                        >
                            onlinevideoworkaround
                            <br/> (TV streams on Linux!)
                        </a>
                    </div>
                </div>
                <div className="panel invisible">Gallery here</div>
            </div>
        </>
    );
}
