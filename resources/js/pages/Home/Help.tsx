import {useEffect, useState} from "react";
import {Link} from "react-router";
import api from "../../lib/axios.ts";
import {Nav} from "./Home.tsx";

type Station = "radio" | "tv";

type Channel = {
    id: number;
    name: string;
    url: string;
};

type ChannelsByStation = {
    radio: Channel[];
    tv: Channel[];
};

export default function Help() {
    return (
        <div className="vcb">
            <Nav/>
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

function HelpSubjects() {
    return (
        <fieldset className="help-subjects links">
            <legend>Subjects</legend>
            <a className="p-2" href="#how-to">How to</a>
            <a className="p-2" href="#online-txt-generator">Online.txt generator</a>
            <a className="p-2" href="#common-issues">Common issues</a>
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
                <p>Every <span className="help-emphasis">odd</span> line is a label, the name you want to give the stream in-game.</p>
                <p>Every <span className="help-emphasis">even</span> line is the URL toward the stream.</p>
                <img className="stream-example" src="/images/ex.png" alt="Example online.txt file with alternating labels and stream URLs"/>
                <p>Once done, do not forget to <span className="help-emphasis">save</span> the file.</p>
            </div>
        </section>
    );
}

function OnlineTxtGenerator() {
    const [station, setStation] = useState<Station>("radio");
    const [channels, setChannels] = useState<ChannelsByStation>({radio: [], tv: []});
    const [selected, setSelected] = useState<number[]>([]);
    const [error, setError] = useState<string>();
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get<ChannelsByStation>("/api/v1/channels")
            .then(response => setChannels(response.data))
            .catch(() => setError("Unable to load channels."))
            .finally(() => setLoading(false));
    }, []);

    const currentChannels = channels[station];

    const toggleChannel = (id: number) => {
        setSelected(current => current.includes(id) ? current.filter(channelId => channelId !== id) : [...current, id]);
    };

    const toggleAll = () => {
        const visibleIds = currentChannels.map(channel => channel.id);
        setSelected(current => visibleIds.every(id => current.includes(id)) ? current.filter(id => !visibleIds.includes(id)) : [...new Set([...current, ...visibleIds])]);
    };

    const changeStation = (nextStation: Station) => {
        setStation(nextStation);
        setSelected([]);
    };

    const generate = () => {
        const chosenChannels = currentChannels
            .filter(channel => selected.includes(channel.id));
        if (chosenChannels.length === 0) {
            return;
        }

        const content = chosenChannels
            .map(channel => channel.name + "\n" + channel.url + "\n")
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
                    <button className={station === "radio" ? "active" : ""} onClick={() => changeStation("radio")}>Radio</button>
                    <button className={station === "tv" ? "active" : ""} onClick={() => changeStation("tv")}>TV</button>
                </div>
                <div className="generator-title">{station === "radio" ? "Radio" : "TV"}</div>
                <div className="generator-list">
                    {error && <p className="generator-message red">{error}</p>}
                    {loading && <p className="generator-message">Loading channels...</p>}
                    {!loading && !error && currentChannels.length === 0 && <p className="generator-message">No channels available.</p>}
                    {currentChannels.map(channel => (
                        <label key={channel.id} className="generator-channel">
                            <span>{channel.name}</span>
                            <input type="checkbox" checked={selected.includes(channel.id)} onChange={() => toggleChannel(channel.id)}/>
                        </label>
                    ))}
                    <label className="generator-channel">
                        <span>Toggle All</span>
                        <input
                            type="checkbox"
                            checked={currentChannels.length > 0 && currentChannels.every(channel => selected.includes(channel.id))}
                            onChange={toggleAll}
                            disabled={currentChannels.length === 0}
                        />
                    </label>
                </div>
                <div className="generator-footer">
                    <button
                        onClick={generate}
                        disabled={!currentChannels.some(channel => selected.includes(channel.id))}
                    >
                        Generate!
                    </button>
                </div>
            </div>
        </section>
    );
}

function CommonIssues() {
    return (
        <section id="common-issues" className="help-section">
            <h2 className="subtitle">Common issues</h2>
            <div className="help-content common-issues-content">
                <h3 className="subtitle">List is empty, I don't see the files I added to online.txt</h3>
                <p>First, make sure you saved your file by doing CTRL+S or File -&gt; Save</p>
                <p>Once done, try refreshing assets directly in the in-game settings</p>
                <h3 className="subtitle">Status stuck to none</h3>
                <p>Make sure the TV is plugged in</p>
                <h3 className="subtitle">Status stuck to failed</h3>
                <p>VotV Community Broadcaster might be down or blocked where you live.</p>
                <p>Try accessing the streams directly in your browser, try those ones:</p>
                <a href="https://radio.votvbroadcast.com/votv.mp3">VotV Community Radio</a>
                <a href="https://tv.votvbroadcast.com/votv.mp4">VotV Community TV</a>
                <h3 className="subtitle">Linux shenanigans</h3>
                <p>Make sure you have this launch option first:</p>
                <p className="help-emphasis">WINE_DO_NOT_CREATE_DXGI_DEVICE_MANAGER=1 %command%</p>
                <p>This makes the radio works flawlessly. As for the TV you will hear the audio but see nothing.</p>
                <p>To make the TV work on Linux please use this <a href="https://github.com/Foxaryse/ArchiveCommunityBranch/tree/main/linux/scripts/onlinevideoworkaround">workaround</a></p>
            </div>
        </section>
    );
}
