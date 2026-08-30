import {useEffect, useState} from "react";
import {Link} from "react-router";
import api from "../lib/axios.ts";

type Station = "radio" | "tv";

type Channel = {
    id: number;
    name: string;
    radio_url: string;
    tv_url: string;
};

export default function Help() {
    return (
        <div className="vcb">
            <HelpNav/>
            <main className="help">
                <h1 className="text-center title text-4xl md:text-5xl lg:text-6xl">Help</h1>
                <HelpSubjects/>
                <HowTo/>
                <OnlineTxtGenerator/>
                <CommonIssues/>
            </main>
        </div>
    );
}

function HelpNav() {
    return (
        <nav>
            <div className="flex flex-row h-16">
                <div className="hidden sm:flex flex-row gap-4 mr-4 ml-auto">
                    <Link className="mt-auto mb-auto p-2 w-28" to="/">Home</Link>
                    <a className="mt-auto mb-auto p-2 w-28" href="https://radio.votvbroadcast.com/votv.mp3">Radio</a>
                    <a className="mt-auto mb-auto p-2 w-28" href="https://tv.votvbroadcast.com/votv.mp4">TV</a>
                    <Link className="mt-auto mb-auto p-2 w-28" to="/upload">Upload</Link>
                    <Link className="mt-auto mb-auto p-2 w-28" to="/login">Login</Link>
                </div>
            </div>
        </nav>
    );
}

function HelpSubjects() {
    return (
        <fieldset className="help-subjects">
            <legend>Subjects</legend>
            <a className="p-2 w-40" href="#how-to">How to</a>
            <a className="p-2 w-60" href="#online-txt-generator">Online.txt generator</a>
            <a className="p-2 w-60" href="#common-issues">Common issues</a>
        </fieldset>
    );
}

function HowTo() {
    return (
        <section id="how-to" className="help-section">
            <h2 className="subtitle">How to</h2>
            <div className="help-content stream-instructions">
                <h3 className="subtitle">Paths</h3>
                <p><span className="path-label">Windows (Radio):</span> %LOCALAPPDATA%\VotV\Assets\radio</p>
                <p><span className="path-label">Windows (TV):</span> %LOCALAPPDATA%\VotV\Assets\tv</p>
                <p><span className="path-label">Linux_steam (Radio):</span> /home/{"{user}"}/.steam/steam/steamapps/compatdata/{"{id_given_for_votv}"}/pfx/drive_c/users/steamuser/AppData/Local/VotV/Assets/radio</p>
                <p><span className="path-label">Linux_steam (TV):</span> /home/{"{user}"}/.steam/steam/steamapps/compatdata/{"{id_given_for_votv}"}/pfx/drive_c/users/steamuser/AppData/Local/VotV/Assets/tv</p>
                <h3 className="subtitle">How to add the streams to your online.txt</h3>
                <p>Every <span className="subtitle">odd</span> line is a label, the name you want to give the stream in-game.</p>
                <p>Every <span className="subtitle">even</span> line is the URL toward the stream.</p>
                <p>Once done, do not forget to <span className="subtitle">save</span> the file.</p>
            </div>
        </section>
    );
}

function OnlineTxtGenerator() {
    const [station, setStation] = useState<Station>("radio");
    const [channels, setChannels] = useState<Channel[]>([]);
    const [selected, setSelected] = useState<number[]>([]);
    const [error, setError] = useState<string>();

    useEffect(() => {
        api.get<Channel[]>("/api/v1/channels")
            .then(response => setChannels(response.data))
            .catch(() => setError("Unable to load channels."));
    }, []);

    const toggleChannel = (id: number) => {
        setSelected(current => current.includes(id) ? current.filter(channelId => channelId !== id) : [...current, id]);
    };

    const toggleAll = () => {
        const visibleIds = channels.map(channel => channel.id);
        setSelected(current => visibleIds.every(id => current.includes(id)) ? current.filter(id => !visibleIds.includes(id)) : [...new Set([...current, ...visibleIds])]);
    };

    const generate = () => {
        const content = channels
            .filter(channel => selected.includes(channel.id))
            .map(channel => `${channel.name}\n${station === "radio" ? channel.radio_url : channel.tv_url}\n`)
            .join("");
        const link = document.createElement("a");
        link.href = URL.createObjectURL(new Blob([content], {type: "text/plain;charset=utf-8"}));
        link.download = "online.txt";
        link.click();
        URL.revokeObjectURL(link.href);
    };

    return (
        <section id="online-txt-generator" className="help-section">
            <h2 className="subtitle">Online.txt generator</h2>
            <div className="generator">
                <div className="generator-tabs">
                    <button className={station === "radio" ? "selected" : ""} onClick={() => setStation("radio")}>Radio</button>
                    <button className={station === "tv" ? "selected" : ""} onClick={() => setStation("tv")}>TV</button>
                </div>
                {error && <p className="red">{error}</p>}
                {!error && channels.length === 0 && <p>Loading channels...</p>}
                {channels.map(channel => (
                    <label key={channel.id} className="generator-channel">
                        <span>{channel.name}</span>
                        <input type="checkbox" checked={selected.includes(channel.id)} onChange={() => toggleChannel(channel.id)}/>
                    </label>
                ))}
                <button onClick={toggleAll}>Toggle all</button>
                <button onClick={generate} disabled={selected.length === 0}>Generate!</button>
            </div>
        </section>
    );
}

function CommonIssues() {
    return (
        <section id="common-issues" className="help-section">
            <h2 className="subtitle">Common issues</h2>
            <div className="help-content">
                <h3 className="subtitle">List is empty, I do not see the files I added to online.txt</h3>
                <p>First, make sure you saved your file with CTRL+S or File → Save.</p>
                <p>Then try refreshing assets directly in the in-game settings.</p>
                <h3 className="subtitle">Status stuck to none</h3>
                <p>Make sure the TV is plugged in.</p>
                <h3 className="subtitle">Status stuck to failed</h3>
                <p>VCB might be down or blocked where you live. Try opening the streams directly:</p>
                <a href="https://radio.votvbroadcast.com/votv.mp3">VotV Community Radio</a>
                <a href="https://tv.votvbroadcast.com/votv.mp4">VotV Community TV</a>
                <h3 className="subtitle">Linux shenanigans</h3>
                <p>Use this launch option:</p>
                <p className="subtitle">WINE_DO_NOT_CREATE_DXGI_DEVICE_MANAGER=1 %command%</p>
                <p>For TV playback on Linux, use the <a href="https://github.com/Foxaryse/ArchiveCommunityBranch/tree/main/linux/scripts/onlinevideoworkaround">workaround</a>.</p>
            </div>
        </section>
    );
}
