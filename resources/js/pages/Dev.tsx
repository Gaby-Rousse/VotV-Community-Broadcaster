export default function Dev() {
    return (
    <div className="tui flex flex-col">
        {/* TOP GRAY BAR */}
        <div className="h-8 relative top-0 bg-(--tui-gray) flex flex-row"></div>
        {/* WINDOW */}
        <div className="outer-border bg-[#AAAAAA] m-auto max-w-162.5 min-w-162.5">
            <div className="text-white h-6.5 leading-6.5 bg-(--tui-darkblue) text-center tracking-[1px]">TITLE</div>
            <div className="p-5 pb-0">
                <div className="text-lg text-(--tui-black)! tracking-[1px]">SUBTITLE</div>
                <div className="my-6.25 text-(--tui-black)!">Content</div>
            </div>

            <div className="px-2.5">
                <div className="bg-black flex flex-col gap-1.25 text-[14px] inner-border p-5 leading-5">
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-gray)!">key:</div><div className="text-(--tui-teal)!">value</div>
                    </div>
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-gray)!">key:</div><div className="text-(--tui-teal)!">value</div>
                    </div>
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-gray)!">key:</div><div className="text-(--tui-teal)!">value</div>
                    </div>
                </div>
            </div>

            <div className="p-5 pt-0">
                <div className="flex flex-col gap-2.5 my-2.5">
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-red)!">■</div><div className="text-(--tui-black)!">Item 1</div>
                    </div>
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-red)!">■</div><div className="text-(--tui-black)!">Item 2</div>
                    </div>
                    <div className="flex flex-row gap-2.5">
                        <div className="text-(--tui-red)!">■</div><div className="text-(--tui-black)!">Item 3</div>
                    </div>
                </div>
                <div className="flex gap-5">
                    <a className="primary" href="http://localhost:8080">PRIMARY BUTTON</a>
                    <a className="secondary" href="http://localhost:8080">SECONDARY BUTTON</a>
                </div>
            </div>


        </div>
        <div className="h-8 relative bottom-0 w-full leading-8 text-center bg-[#00AAAA]">VotV Community Broadcaster v1.0.0 Saki Edition</div>
    </div>

    );
}
